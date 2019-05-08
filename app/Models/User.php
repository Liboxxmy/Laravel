<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
//use Illuminate\Support\Facades\Auth;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function gravatar($size='100'){
    	$hash=md5(strtolower(trim($this->attributes['email'])));
	    return "http://www.gravatar.com/avatar/$hash?s=$size";
    }
    public static function boot(){
        parent::boot();
        static::creating(function($user){
            $user->activation_token=str_random(30);
        });
    }
    public function sendPasswordResetNotification($token){
        $this->notify(new ResetPassword($token));
    }
    /**
     * 指明一个用户拥有多条微博
     */
    public function statuses(){
        return $this->hasMany(Status::class);
    }

    /**
     * @return mixed
     * 这里需要注意的是 Auth::user()->followings 的用法。我们在 User 模型里定义了关联方法 followings()，关联关系定义好后，
     * 我们就可以通过访问 followings 属性直接获取到关注用户的 集合。这是 Laravel Eloquent 提供的「动态属性」属性功能，
     * 我们可以像在访问模型中定义的属性一样，来访问所有的关联方法。还有一点需要注意的是 $user->followings 与 $user->followings()
     * 调用时返回的数据是不一样的， $user->followings 返回的是 Eloquent：集合 。而 $user->followings() 返回的是 数据库请求构建器 ，followings() 的情况下，
     * 你需要使用：$user->followings()->get()
     */
    public function feed(){
        $user_ids=Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids,Auth::user()->id);
        return Status::whereIn('user_id',$user_ids)
            ->with('user')
            ->orderBy('created_at','desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 在 Laravel 中会默认将两个关联模型的名称进行合并，并按照字母排序，因此我们生成的关联关系表名称会是 user_user。我们也可以自定义生成的名称，把关联表名改为 followers
     * belongsToMany 方法的第三个参数 user_id 是定义在关联中的模型外键名，而第四个参数 follower_id 则是要合并的模型外键名。
     */
    public function followers(){
        return $this->belongsToMany(User::class,'followers','user_id','follower_id');
    }
    public function followings(){
        return $this->belongsToMany(User::class,'followers', 'follower_id', 'user_id');
    }
    public function follow($user_ids){
        if(!is_array($user_ids)){
            $user_ids=compact('user_ids');
        }
        $this->followings()->sync($user_ids,false);
    }
    public function unfollow($user_ids){
        if(!is_array($user_ids)){
            $user_ids=compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }
    public function isFollowing($user_id){
        return $this->followings->contains($user_id);
    }




























}
