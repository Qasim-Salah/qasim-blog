<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{


    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password','role_id'];

    public $timestamps = true;




//    protected static function boot()
//    {
//        parent::boot();
//        static::addGlobalScope(new GlobalScope);
//        static::addGlobalScope(new GlobalScopeID);
//    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.' . $this->id;
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post', 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'user_id', 'id');
    }

    public function roles()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function status()
    {
        return $this->status == '1' ? 'Active' : 'Inactive';
    }

    public function userImage()
    {
        return $this->user_image != '' ? asset('assets/users/' . $this->user_image) : asset('assets/users/default.png');
    }

}
