<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    //构造函数
    public function __construct()
    {
        //未登录允许访问的方法
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        //已登录用户无法访问创建
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    //首页  
    public function index(){

        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    //注册用户方法
    public function create()
    {
        return view('users.create');
    }

    //個人用戶展示頁面
    public function show(User $user)
    {

        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);

        return view('users.show', compact('user','statuses'));
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

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
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


    //删除请求处理
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }


    //发送邮件
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }


    //验证激活页面
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    //关注人列表
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    //关注粉丝列表
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name . '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }


}