<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    //注册用户方法
    public function create()
    {
        return view('users.create');
    }

    //個人用戶展示頁面
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }


    //验证表单相关的数据
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        return;
    }

}
