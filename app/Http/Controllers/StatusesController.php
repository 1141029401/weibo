<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class StatusesController extends Controller
{	
	//调用AUTH中间件，过滤未登陆请求
    public function __construct()
    {
        $this->middleware('auth');
    }
 	
    //创建请求
 	public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:140'
        ]);

        Auth::user()->statuses()->create([
            'content' => $request['content']
        ]);

        session()->flash('success', '发布成功！');
        
        return redirect()->back();
    }   
}
