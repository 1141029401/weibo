<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    //构造函数
    public function __construct()
    {

        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);

    }


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
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
    }

    //编辑页面
    public function edit(User $user)
    {
        //验证不同用户
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }


    //编辑请求处理
    public function update(User $user, Request $request)
    {
        //验证不同用户
        $this->authorize('update', $user);

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
    }

}
