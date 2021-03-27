<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'image',
        'status',
        'bio',
        'receive_email'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class,);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getPhotoAttribute($val)
    {
        return ($val !== null) ? asset('assets/images/users/' . $val) : "";
    }

    public function scopeActive($query)
    {

        return $query->where('status', 1);
    }
}
