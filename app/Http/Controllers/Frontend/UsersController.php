<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CommentRequest;
use App\Http\Requests\Frontend\infoRequest;
use App\Http\Requests\Frontend\Story_postRequest;
use App\Http\Requests\passwordRequest;
use App\Models\Category as CategoryModel;
use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;

class UsersController extends Controller
{

    public function index()
    {
        $posts = auth()->user()->posts()
            ->with(['user'])
            ->withCount('comments')
            ->orderBy('id', 'desc')
            ->paginate(PAGINATION_DASHBOARD);
        return view('frontend.users.dashboard', compact('posts'));
    }

    public function edit_info()
    {
        return view('frontend.users.edit_info');
    }

    public function update_info(infoRequest $request)
    {
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('register', $request->image);
        }
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['bio'] = $request->bio;
        $data['receive_email'] = $request->receive_email;
        $data['image'] = $fileName;


        $update = auth()->user()->update($data);

        if ($update)
            return redirect()->route('users.comments')->with(['message' => 'Information updated successfully', 'alert-type' => 'success',]);

        return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function update_password(passwordRequest $request)
    {

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $update = $user->update([
                'password' => bcrypt($request->password),
            ]);
        }
        if ($update)
            return redirect()->route('users.comments')->with(['message' => 'Information updated successfully', 'alert-type' => 'success',]);
        return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function create_post()
    {
        $categories = CategoryModel::active()->pluck('name', 'id');
        return view('frontend.users.create_post', compact('categories'));
    }

    public function store_post(Story_postRequest $request)
    {
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }
        $data['title'] = Purify::clean($request->title);
        $data['description'] = Purify::clean($request->description);
        $data['status'] = $request->status;
        $data['comment_able'] = $request->comment_able;
        $data['category_id'] = $request->category_id;
        $data['image'] = $fileName;
        $post = auth()->user()->posts()->create($data);

        if ($request->status == 1)
            Cache::forget('recent_posts');

        if ($post)
            return redirect()->route('users.dashboard')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        return redirect()->route('users.post.create') - with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function edit_post($post_id)
    {
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_id', auth()->id())->first();

        if ($post) {
            $categories = CategoryModel::active()->pluck('name', 'id');
            return view('frontend.users.edit_post', compact('post', 'categories'));
        }

        return redirect()->route('Frontend.index');

    }

    public function update_post(Story_postRequest $request, $post_id)
    {
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_id', auth()->id())->first();

        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }
        $data['title'] = Purify::clean($request->title);
        $data['description'] = Purify::clean($request->description);
        $data['status'] = $request->status;
        $data['comment_able'] = $request->comment_able;
        $data['category_id'] = $request->category_id;
        $data['image'] = $fileName;

        $post->update($data);

        if ($post)
            return redirect()->route('users.dashboard')->with(['message' => 'Post updated successfully', 'alert-type' => 'success']);

        return redirect()->route('users.dashboard')->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function destroy_post($post_id)
    {
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

        if ($post) {
            if (File::exists('assets/posts/' . $post->image))
                unlink('assets/posts/' . $post->image);
            $post->comments()->delete();
            $post->delete();
            Cache::forget('recent_posts');

            return redirect()->route('users.dashboard')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('users.dashboard')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function show_comments(Request $request)
    {
        $comments = CommentModel::query();

        if (!empty($request->post)) {
            $comments = $comments->wherePostId($request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $comments = $comments->whereIn('post_id', $posts_id);;
        }
        $comments = $comments->paginate(PAGINATION_DASHBOARD);

        return view('frontend.users.comments', compact('comments'));
    }

    public function edit_comment($comment_id)
    {

        $comment = CommentModel::where('id', $comment_id)->whereHas('post', function ($query) {
            $query->where('posts.user_id', auth()->id());
        })->first();

        if ($comment)
            return view('frontend.users.edit_comment', compact('comment'));

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function update_comment(CommentRequest $request, $comment_id)
    {

        $comment = CommentModel::where('id', $comment_id)->whereHas('post', function ($query) {
            $query->where('posts.user_id', auth()->id());
        })->first();

        if ($comment) {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['url'] = $request->url != '' ? $request->url : null;
            $data['status'] = $request->status;
            $data['comment'] = Purify::clean($request->comment);

            $comment->update($data);

            if ($request->status == 1) {
                Cache::forget('recent_comments');
            }

            return redirect()->route('users.comments')->with(['message' => 'Comment updated successfully', 'alert-type' => 'success',]);
        }
        return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

    public function destroy_comment($comment_id)
    {
        $comment = CommentModel::where('id', $comment_id)->whereHas('post', function ($query) {
            $query->where('posts.user_id', auth()->id());
        })->first();

        if ($comment) {
            $comment->delete();
            Cache::forget('recent_comments');

            return redirect()->back()->with(['message' => 'Comment deleted successfully', 'alert-type' => 'success',]);
        }
        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

}
