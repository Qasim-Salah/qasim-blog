<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\Story_postRequest;
use App\Models\Category as CategoryModel;
use App\Models\Post as PostModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stevebauman\Purify\Facades\Purify;

class PostsController extends Controller
{

    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $categoryId = !empty(\request()->category_id) ? \request()->category_id : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $posts = PostModel::latest()->post();

        if (!empty($keyword)) {
            $posts = $posts->where('title', 'LIKE', '%' . $keyword . '%');
        }
        if (!empty($categoryId)) {
            $posts = $posts->where('category_id', $categoryId);
        }
        if (!empty($status)) {
            $posts = $posts->where('status', $status);
        }

        $posts = $posts->orderBy($sort_by, $order_by);
        $posts = $posts->paginate($limit_by);

        $categories = CategoryModel::pluck('name', 'id');
        return view('backend.posts.index', compact('categories', 'posts'));

    }

    public function create()
    {
        $categories = CategoryModel::pluck('name', 'id');
        return view('backend.posts.create', compact('categories'));
    }

    public function store(Story_postRequest $request)
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
            return redirect()->route('admin.posts.index')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function show($id)
    {
        $post = PostModel::with([ 'category', 'user', 'comments'])
            ->where('id', $id)
            ->post()
            ->first();
        return view('backend.posts.show', compact('post'));
    }

    public function edit($id)
    {
        $categories = CategoryModel::pluck('name', 'id');
        $post = PostModel::findOrfail($id);
        $post->post();

        return view('backend.posts.edit', compact('categories', 'post'));
    }

    public function update(Story_postRequest $request, $id)
    {
        $post = PostModel::findOrfail($id);

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
            return redirect()->route('admin.posts.index')->with(['message' => 'Post updated successfully', 'alert-type' => 'success',]);

        return redirect()->route('admin.posts.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function destroy($id)
    {
        $post = PostModel::findOrfail($id);

        if ($post) {
            if (File::exists('assets/posts/' . $post->image))
            $post->comments()->delete();
            $post->delete();
            Cache::forget('recent_posts');
            return redirect()->route('admin.posts.index')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.posts.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

}
