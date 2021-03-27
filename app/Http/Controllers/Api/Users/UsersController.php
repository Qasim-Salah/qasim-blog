<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;

use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UsersCategoriesResource;
use App\Http\Resources\Users\UsersPostCommentsResource;
use App\Http\Resources\Users\UsersPostResource;
use App\Http\Resources\Users\UsersPostsResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostMedia;
use App\Scopes\GlobalScope;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    use GeneralTrait;

//    public function getNotifications()
//    {
//        return [
//            'read' => auth()->user()->readNotifications,
//            'unread' => auth()->user()->unreadNotifications,
//        ];
//    }
//
//    public function markAsRead(Request $request)
//    {
//        return auth()->user()->notifications->where('id', $request->id)->markAsRead();
//    }

    public function user_information()
    {
        $user = \auth()->user();
        return $this->returnData('user_information', new UserResource($user));
    }

    public function update_user_information(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'email' => 'required|email',
                'mobile' => 'required|numeric',
                'bio' => 'nullable|min:10',
                'receive_email' => 'required',
                'user_image' => 'nullable|image|max:20000,mimes:jpeg,jpg,png'
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                $code = $this->returnCodeAccordingToInput($validation);
                return $this->returnValidationError($code, $validation);
            }

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
                return $this->returnSuccessMessage('Information updated successfully', 200);

        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function update_user_password(Request $request)
    {
        try {
            $rules = [
                'current_password' => 'required',
                'password' => 'required|confirmed'
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                $code = $this->returnCodeAccordingToInput($validation);
                return $this->returnValidationError($code, $validation);
            }
            $user = auth()->user();
            if (Hash::check($request->current_password, $user->password)) {
                $update = $user->update([
                    'password' => bcrypt($request->password),
                ]);
            }
            if ($update)
                return $this->returnSuccessMessage('Password updated successfully', 200);
        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function my_posts()
    {
        $user = Auth::user();
        $posts = $user->posts;
        return $this->returnData('my_posts', UsersPostsResource::collection($posts));
    }

    public function create_post()
    {
        $categories = Category::get();

        return $this->returnData( 'create_post',UsersCategoriesResource::collection($categories));
    }

    public function store_post(Request $request)
    {
        try {
            $rules = [
                'title' => 'required',
                'description' => 'required|min:50',
                'status' => 'required',
                'comment_able' => 'required',
                'category_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                $code = $this->returnCodeAccordingToInput($validation);
                return $this->returnValidationError($code, $validation);
            }
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

            return $this->returnSuccessMessage('Post created successfully', 200);

        } catch (\Exception $ex) {

            DB::rollback();
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function edit_post($post_id)
    {
        try {
            $post = Post::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

            if ($post) {
                $categories = Category::get();
                return ['post' => new UsersPostResource($post), 'categories' => UsersCategoriesResource::collection($categories)];
            }
        } catch (\Exception $ex) {

            return $this->returnError('E001', 'Unauthorized');
        }
    }

    public function update_post(Request $request, $post_id)
    {
        try {
            $rules = [
                'title' => 'required',
                'description' => 'required|min:50',
                'status' => 'required',
                'comment_able' => 'required',
                'category_id' => 'required',
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                $code = $this->returnCodeAccordingToInput($validation);
                return $this->returnValidationError($code, $validation);
            }

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
                return $this->returnSuccessMessage('Post update successfully', 200);
            }
        } catch (\Exception $ex) {
            DB::rollback();

            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function delete_post($post_id)
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
                $post->delete();
                $post->media()->delete();
                $post->comments()->delete();

                DB::commit();

                return $this->returnSuccessMessage('Post deleted successfully', 200);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function delete_post_media($media_id)
    {
        try {
            $media = PostMedia::where('id', $media_id)->first();
            if ($media) {
                if (File::exists('assets/posts/' . $media->file_name)) {
                    unlink('assets/posts/' . $media->file_name);
                }
                $media->delete();
                return $this->returnSuccessMessage('Media deleted successfully', 200);
            }
        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function all_comments(Request $request)
    {
        $comments = Comment::query();

        if (isset($request->post) && $request->post != '') {
            $comments = $comments->wherePostId($request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $comments = $comments->whereIn('post_id', $posts_id);;
        }
        $comments = $comments->withoutGlobalScope(GlobalScope::class)->get();


        return $this->returnData('all_comment',UsersPostCommentsResource::collection($comments));
    }

    public function edit_comment($id)
    {
        try {

            $comment = Comment::where('id', $id)->whereHas('post', function ($query) {
                $query->where('posts.user_id', auth()->id());
            })->withoutGlobalScope(GlobalScope::class)->first();

            if ($comment) {
                return $this->returnData('edit_comment', new UsersPostCommentsResource($comment));
            }
        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function update_comment(Request $request, $comment_id)
    {
        try {
            $rules = [
                'name' => 'required',
                'email' => 'required|email',
                'url' => 'nullable|url',
                'status' => 'required',
                'comment' => 'required',
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                $code = $this->returnCodeAccordingToInput($validation);
                return $this->returnValidationError($code, $validation);
            }

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
                return $this->returnSuccessMessage('Comment updated successfully', 200);
            }
        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }

    }

    public function delete_comment($id)
    {
        try {
            $comment = Comment::withoutGlobalScope(GlobalScope::class)->where('id', $id)->whereHas('post', function ($query) {
                $query->where('posts.user_id', auth()->id());
            })->first();

            if ($comment) {
                $comment->delete();

                Cache::forget('recent_comments');
                return $this->returnSuccessMessage('Comment deleted successfully', 200);
            }
        } catch (\Exception $ex) {
            return $this->returnError('E001', 'Something was wrong');
        }
    }


    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['errors' => false, 'message' => 'Successfully logged out']);
    }

}
