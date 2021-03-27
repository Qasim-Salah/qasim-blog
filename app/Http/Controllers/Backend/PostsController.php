<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Story_postRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostMedia;
use App\Scopes\GlobalScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
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

        $posts = Post::withoutGlobalScope(GlobalScope::class)
            ->post();

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

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        return view('backend.posts.index', compact('categories', 'posts'));

    }

    public function create()
    {

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        return view('backend.posts.create', compact('categories'));
    }

    public function store(Story_postRequest $request)
    {
        try {

            DB::beginTransaction();
            $data['title'] = Purify::clean($request->title);
            $data['description'] = Purify::clean($request->description);
            $data['status'] = $request->status;
            $data['post_type'] = 'post';
            $data['comment_able'] = $request->comment_able;
            $data['category_id'] = $request->category_id;

            $post = auth()->user()->posts()->create($data);

            if ($request->images && count($request->images) > 0) {
                $i = 1;
                foreach ($request->images as $file) {
                    $filename = $post->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                    $file_size = $file->getSize();
                    $file_type = $file->getMimeType();
                    $path = public_path('assets/posts/' . $filename);
                    Image::make($file->getRealPath())->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);

                    $post->media()->create([
                        'file_name' => $filename,
                        'file_size' => $file_size,
                        'file_type' => $file_type,
                    ]);
                    $i++;
                }
            }

            if ($request->status == 1) {
                Cache::forget('recent_posts');
            }
            DB::commit();

            return redirect()->route('admin.posts.index')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function show($id)
    {

        $post = Post::with(['media', 'category', 'user', 'comments'])
            ->where('id', $id)
            ->post()
            ->withoutGlobalScope(GlobalScope::class)
            ->first();
        return view('backend.posts.show', compact('post'));
    }

    public function edit($id)
    {

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        $post = Post:: withoutGlobalScope(GlobalScope::class)->with(['media'])->where('id', $id)->post()->first();

        return view('backend.posts.edit', compact('categories', 'post'));
    }

    public function update(Story_postRequest $request, $id)
    {
        try {
            $post = Post:: withoutGlobalScope(GlobalScope::class)->where('id', $id)->post()->first();

            if ($post) {
                $data['title'] = $request->title;
                $data['slug'] = null;
                $data['description'] = Purify::clean($request->description);
                $data['status'] = $request->status;
                $data['comment_able'] = $request->comment_able;
                $data['category_id'] = $request->category_id;
                DB::beginTransaction();
                $post->update($data);

                if ($request->images && count($request->images) > 0) {
                    $i = 1;
                    foreach ($request->images as $file) {
                        $filename = $post->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                        $file_size = $file->getSize();
                        $file_type = $file->getMimeType();
                        $path = public_path('assets/posts/' . $filename);
                        Image::make($file->getRealPath())->resize(800, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($path, 100);

                        $post->media()->create([
                            'file_name' => $filename,
                            'file_size' => $file_size,
                            'file_type' => $file_type,
                        ]);
                        $i++;
                    }
                }
                DB::commit();
                return redirect()->route('admin.posts.index')->with(['message' => 'Post updated successfully', 'alert-type' => 'success',]);
            }

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->route('admin.posts.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post:: withoutGlobalScope(GlobalScope::class)->Where('id', $id)->first();

            if ($post) {
                if ($post->media->count() > 0) {
                    foreach ($post->media as $media) {
                        if (File::exists('assets/posts/' . $media->file_name)) {
                            unlink('assets/posts/' . $media->file_name);
                        }
                    }
                }
                DB::beginTransaction();
                $post->media()->delete();
                $post->comments()->delete();
                $post->delete();
                DB::commit();

                return redirect()->route('admin.posts.index')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->route('admin.posts.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function removeImage(Request $request)
    {

        $media = PostMedia::where('id', $request->media_id)->first();
        if ($media) {
            if (File::exists('assets/posts/' . $media->file_name)) {
                unlink('assets/posts/' . $media->file_name);
            }
            $media->delete();
            return true;
        }
        return false;
    }

}
