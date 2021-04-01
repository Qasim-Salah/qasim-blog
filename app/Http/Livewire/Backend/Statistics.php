<?php

namespace App\Http\Livewire\Backend;

use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use App\Models\User as UserModel;
use Livewire\Component;

class Statistics extends Component
{
    public function render()
    {

        $all_users = UserModel::active()->count();

        $active_posts = PostModel::post()->count();
        $inactive_posts = PostModel::active()->post()->count();
        $active_comments = CommentModel::active()->count();


        return view('livewire.backend.statistics', [
            'all_users' => $all_users,
            'active_posts' => $active_posts,
            'inactive_posts' => $inactive_posts,
            'active_comments' => $active_comments,
        ]);
    }
}
