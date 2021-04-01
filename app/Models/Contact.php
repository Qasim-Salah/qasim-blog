<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

    protected $table = 'contacts';
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'title',
        'message',
        'status',
    ];

    public function status()
    {
        return $this->status == 1 ? 'Read' : 'New';
    }

}
