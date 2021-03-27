<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\infoRequest;
use App\Http\Requests\passwordRequest;
use App\Http\Requests\Story_postRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostMedia;
use App\Scopes\GlobalScope;
use App\Scopes\GlobalScopeID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;

class UsersController extends Controller
{

    public function index()
    {
        $posts = auth()->user()->posts()->with(['media', 'user'])
            ->withCount('comments')
            ->withoutGlobalScope(GlobalScope::class)
            ->paginate(PAGINATION_DASHBOARD);
        return view('frontend.users.dashboard', compact('posts'));
    }

    public function edit_info()
    {
        return view('frontend.users.edit_info');
    }

    public function update_info(infoRequest $request)
    {
        try {

            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['mobile'] = $request->mobile;
            $data['bio'] = $request->bio;
            $data['receive_email'] = $request->receive_email;

            if ($image = $request->file('user_image')) {
                if (auth()->user()->user_image != '') {
                    if (File::exists('/assets/users/' . auth()->user()->user_image)) {
                        unlink('/assets/users/' . auth()->user()->user_image);
                    }
                }
                $filename = Str::slug(auth()->user()->username) . '.' . $image->getClientOriginalExtension();
                $path = public_path('assets/users/' . $filename);
                Image::make($image->getRealPath())->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);

                $data['user_image'] = $filename;
            }

            $update = auth()->user()->update($data);

            if ($update)
                return redirect()->route('users.comments')->with(['message' => 'Information updated successfully', 'alert-type' => 'success',]);

        } catch (\Exception $ex) {
            return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function update_password(passwordRequest $request)
    {
        try {

            $user = auth()->user();
            if (Hash::check($request->current_password, $user->password)) {
                $update = $user->update([
                    'password' => bcrypt($request->password),
                ]);
            }
            if ($update)
                return redirect()->route('users.comments')->with(['message' => 'Information updated successfully', 'alert-type' => 'success',]);
        } catch (\Exception $ex) {
            return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function create_post()
    {
        $categories = Category::pluck('name', 'id');
        return view('frontend.users.create_post', compact('categories'));
    }

    public function store_post(Story_postRequest $request)
    {
        try {

            DB::beginTransaction();
            $data['title'] = Purify::clean($request->title);
            $data['description'] = Purify::clean($request->description);
            $data['status'] = $request->status;
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

            return redirect()->route('users.dashboard')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->route('users.post.create') - with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function edit_post($post_id)
    {
        try {
            $post = Post::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

            if ($post) {
                $categories = Category::pluck('name', 'id');
                return view('frontend.users.edit_post', compact('post', 'categories'));
            }
        } catch (\Exception $ex) {

            return redirect()->route('frontend.index');
        }
    }

    public function update_post(Story_postRequest $request, $post_id)
    {
        try {
            $post = Post::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();
            if ($post) {
                $data['title'] = Purify::clean($request->title);
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
                return redirect()->route('users.dashboard')->with(['message' => 'Post updated successfully', 'alert-type' => 'success']);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            return redirect()->route('users.dashboard')->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function destroy_post($post_id)
    {
        try {
            $post = Post::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

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

                return redirect()->route('users.dashboard')->with(['message' => 'Post deleted successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->route('users.dashboard')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function destroy_post_media($media_id)
    {
        try {
            $media = PostMedia::where('id', $media_id)->first();
            if ($media) {
                if (File::exists('assets/posts/' . $media->file_name)) {
                    unlink('assets/posts/' . $media->file_name);
                }
                $media->delete();
                return true;
            }
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function show_comments(Request $request)
    {
        $comments = Comment::query();

        if (isset($request->post) && $request->post != '') {
            $comments = $comments->wherePostId($request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $comments = $comments->whereIn('post_id', $posts_id);;
        }
        $comments = $comments->withoutGlobalScope(GlobalScope::class);
        $comments = $comments->paginate(PAGINATION_DASHBOARD);

        return view('frontend.users.comments', compact('comments'));
    }

    public function edit_comment($comment_id)
    {
        try {

            $comment = Comment::where('id', $comment_id)->whereHas('post', function ($query) {
                $query->where('posts.user_id', auth()->id());
            })->withoutGlobalScope(GlobalScope::class)->first();

            if ($comment) {
                return view('frontend.users.edit_comment', compact('comment'));
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function update_comment(CommentRequest $request, $comment_id)
    {
        try {

            $comment = Comment::where('id', $comment_id)->whereHas('post', function ($query) {
                $query->where('posts.user_id', auth()->id());
            })->withoutGlobalScope(GlobalScope::class)->first();

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
        } catch (\Exception $ex) {
            return redirect()->route('users.comments')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }

    }

    public function destroy_comment($comment_id)
    {
        try {
            $comment = Comment::where('id', $comment_id)->whereHas('post', function ($query) {
                $query->where('posts.user_id', auth()->id());
            })->withoutGlobalScope(GlobalScope::class)->first();

            if ($comment) {
                $comment->delete();

                Cache::forget('recent_comments');

                return redirect()->back()->with(['message' => 'Comment deleted successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

}
