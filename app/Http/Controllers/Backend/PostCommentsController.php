<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\categoryRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\Story_postRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Scopes\GlobalScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;

class PostCommentsController extends Controller
{
    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $postId = !empty(\request()->post_id) ? \request()->post_id : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $comments = Comment::query()->withoutGlobalScope(GlobalScope::class);

        if (!empty($keyword)) {
            $comments = $comments->where('name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('comment', 'LIKE', '%' . $keyword . '%');
        }
        if (!empty($postId)) {
            $comments = $comments->where('post_id', $postId);
        }
        if (!empty($status)) {
            $comments = $comments->where('status', $status);
        }

        $comments = $comments->orderBy($sort_by, $order_by);
        $comments = $comments->paginate($limit_by);

        $posts = Post:: withoutGlobalScope(GlobalScope::class)
            ->post()
            ->pluck('title', 'id');

        return view('backend.post_comments.index', compact('comments', 'posts'));

    }

    public function create()
    {
        //
    }

    public function store(categoryRequest $request)
    {
        //
    }

    public function show($id)
    {
//
    }

    public function edit($id)
    {

        $comment = Comment:: withoutGlobalScope(GlobalScope::class)->where('id', $id)->first();
        return view('backend.post_comments.edit', compact('comment'));
    }

    public function update(CommentRequest $request, $id)
    {
        try {
            $comment = Comment::withoutGlobalScope(GlobalScope::class)->where('id', $id)->first();

            if ($comment) {
                $data['name'] = $request->name;
                $data['email'] = $request->email;
                $data['url'] = $request->url;
                $data['status'] = $request->status;
                $data['comment'] = Purify::clean($request->comment);

                $comment->update($data);
                Cache::forget('recent_comments');
                return redirect()->route('admin.post_comments.index')->with(['message' => 'Post updated successfully', 'alert-type' => 'success',]);
            }

        } catch (\Exception $ex) {

            return redirect()->route('admin.post_comments.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment:: withoutGlobalScope(GlobalScope::class)->where('id', $id)->first();

            $comment->delete();
            return redirect()->route('admin.post_comments.index')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);

        } catch (\Exception $ex) {

            return redirect()->route('admin.post_comments.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

}
