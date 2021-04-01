<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\categoryRequest;
use App\Http\Requests\Frontend\CommentRequest;
use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use Illuminate\Support\Facades\Cache;
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

        $comments = CommentModel::query();

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

        $posts = PostModel::post()->pluck('title', 'id');

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

        $comment = CommentModel:: findOrfail($id);
        return view('backend.post_comments.edit', compact('comment'));
    }

    public function update(CommentRequest $request, $id)
    {
        $comment = CommentModel::findOrfail($id);

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

        return redirect()->route('admin.post_comments.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

    public function destroy($id)
    {
        $comment = CommentModel:: findOrfail($id);

        if ($comment) {

            $comment->delete();
            return redirect()->route('admin.post_comments.index')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.post_comments.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

}
