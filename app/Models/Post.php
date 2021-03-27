<?php

namespace App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;


class Post extends Model
{
    use Sluggable;

    protected $table = 'posts';
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'post_type',
        'image',
        'comment_able',
        'user_id',
        'category_id'
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function scopePost($query)
    {
        return $query->where('post_type', 'post');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function scopeCommentAble($query)
    {
        return $query->where('comment_able', '1');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    public function status()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }
    public function getPhotoAttribute($val)
    {
        return ($val !== null) ? asset('assets/images/posts/' . $val) : "";
    }


}
