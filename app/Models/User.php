<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,  Notifiable;

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

    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('assets/users/' . $val) : "";
    }

    public function scopeActive($query)
    {

        return $query->where('status', 1);
    }

    public function status()
    {
        return $this->status == '1' ? 'Active' : 'Inactive';
    }

    public function userImage()
    {
        return $this->image != '' ? asset('assets/users/' . $this->image) : asset('assets/users/default.png');
    }

}
