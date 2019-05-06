<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{
    public function __construct(){
        $this->middleware('guest',[
           'only'=>['create']
        ]);
    }
    public function create(){
        return view('sessions.create');
    }
    public function store(Request $request){
        $res=$this->validate($request,[
            'email'=>'required|email|max:255',
            'password'=>'required'
        ]);
        $email=$request->email;
        $password=$request->password;
       /* if(Auth::attempt(['email'=>$email,'password'=>$password])){

        }else{

        }*/
        if(Auth::attempt($res,$request->has('remember'))){
            if(Auth::user()->activated){
                session()->flash('success','欢迎回来~');
                return redirect()->intended(route('users.show',[Auth::user()]));
            }else{
                Auth::logout();
                session()->flash('waring','账号未激活，检查邮箱邮件进行激活');
                return redirect('/');
            }
        }else{
            session()->flash("danger","error info");
            return redirect()->back();
        }
        return;
    }
    public function destroy(){
        Auth::logout();
        session()->flash('success','您已成功退出！');
        return redirect('login');
    }
}
