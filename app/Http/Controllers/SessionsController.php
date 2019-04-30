<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{
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
	    session()->flash('success','欢迎回来~');
	    return redirect()->route('users.show',[Auth::user()]);
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
