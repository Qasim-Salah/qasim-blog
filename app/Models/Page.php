<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
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

    public function scopePage($query)
    {
        return $query->where('post_type', 'page');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
