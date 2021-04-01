<?php

namespace App\Http\Livewire\Backend;

use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use Livewire\Component;

class LastPostComments extends Component
{
    public function render()
    {

        $posts = PostModel::post()->latest()->withCount('comments')->take(5)->get();
        $comments = CommentModel::latest()->take(5)->get();

        return view('livewire.backend.last-post-comments', [
            'posts' => $posts,
            'comments'  => $comments,
        ]);
    }
}
