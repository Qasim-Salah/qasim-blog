<?php

namespace App\Http\Livewire\Backend;

use App\Models\Comment;
use App\Models\Post;
use App\Scopes\GlobalScope;
use Livewire\Component;

class LastPostComments extends Component
{
    public function render()
    {

        $posts = Post::withoutGlobalScope(GlobalScope::class)->post()->withCount('comments')->take(5)->get();
        $comments = Comment::withoutGlobalScope(GlobalScope::class)->take(5)->get();

        return view('livewire.backend.last-post-comments', [
            'posts' => $posts,
            'comments'  => $comments,
        ]);
    }
}
