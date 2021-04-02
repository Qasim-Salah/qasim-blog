<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;

use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UsersCategoriesResource;
use App\Http\Resources\Users\UsersPostCommentsResource;
use App\Http\Resources\Users\UsersPostResource;
use App\Http\Resources\Users\UsersPostsResource;
use App\Models\Category as CategoryModel;
use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
            'bio' => 'nullable|min:10',
            'receive_email' => 'required',
            'image' => 'nullable|image|max:20000,mimes:jpeg,jpg,png'
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('register', $request->image);
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['bio'] = $request->bio;
        $data['image'] = $fileName;

        $data['receive_email'] = $request->receive_email;

        $update = auth()->user()->update($data);

        if ($update)
            return $this->returnSuccessMessage('Information updated successfully', 200);

        return $this->returnError('E001', 'Something was wrong');

    }

    public function update_user_password(Request $request)
    {
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
            return $this->returnError('E001', 'Something was wrong');

    }

    public function my_posts()
    {
        $user = Auth::user();
        $posts = $user->posts;
        return $this->returnData('my_posts', UsersPostsResource::collection($posts));
    }

    public function create_post()
    {
        $categories = CategoryModel::acyive()->get();

        return $this->returnData('create_post', UsersCategoriesResource::collection($categories));
    }

    public function store_post(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required|min:50',
            'status' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',
            'comment_able' => 'required',
            'category_id' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }
        $data['title'] = Purify::clean($request->title);
        $data['description'] = Purify::clean($request->description);
        $data['status'] = $request->status;
        $data['image'] = $fileName;
        $data['comment_able'] = $request->comment_able;
        $data['category_id'] = $request->category_id;


        $post = auth()->user()->posts()->create($data);

        if ($request->status == 1)
            Cache::forget('recent_posts');

        if ($post)
            return $this->returnSuccessMessage('Post created successfully', 200);

        return $this->returnError('E001', 'Something was wrong');

    }

    public function edit_post($post_id)
    {
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

        if ($post) {
            $categories = CategoryModel::active()->get();
            return ['post' => new UsersPostResource($post), 'categories' => UsersCategoriesResource::collection($categories)];
        }

        return $this->returnError('E001', 'Unauthorized');
    }

    public function update_post(Request $request, $post_id)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required|min:50',
            'status' => 'required',
            'comment_able' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',
            'category_id' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }

        if ($post) {
            $data['title'] = Purify::clean($request->title);
            $data['description'] = Purify::clean($request->description);
            $data['status'] = $request->status;
            $data['comment_able'] = $request->comment_able;
            $data['category_id'] = $request->category_id;
            $data['image'] = $fileName;

            $post->update($data);

            return $this->returnSuccessMessage('Post update successfully', 200);
        }

        return $this->returnError('E001', 'Something was wrong');

    }

    public function delete_post($post_id)
    {
        $post = PostModel::where('slug', $post_id)->orWhere('id', $post_id)->where('user_Id', auth()->id())->first();

        if ($post) {
            $post->delete();
            $post->comments()->delete();
            return $this->returnSuccessMessage('Post deleted successfully', 200);

        }

        return $this->returnError('E001', 'Something was wrong');

    }

    public function all_comments(Request $request)
    {
        $comments = CommentModel::query();

        if (!empty($request->post)) {
            $comments = $comments->wherePostId($request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $comments = $comments->whereIn('post_id', $posts_id);;
        }
        $comments = $comments->latest();
        return $this->returnData('all_comment', UsersPostCommentsResource::collection($comments));
    }

    public function edit_comment($id)
    {
        $comment = CommentModel::where('id', $id)->whereHas('post', function ($query) {
            $query->where('posts.user_id', auth()->id());
        })->first();

        if ($comment)
            return $this->returnData('edit_comment', new UsersPostCommentsResource($comment));

        return $this->returnError('E001', 'Something was wrong');
    }

    public function update_comment(Request $request, $comment_id)
    {
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
            return $this->returnSuccessMessage('Comment updated successfully', 200);
        }
        return $this->returnError('E001', 'Something was wrong');

    }

    public function delete_comment($id)
    {
        $comment = CommentModel::where('id', $id)->whereHas('post', function ($query) {
            $query->where('posts.user_id', auth()->id());
        })->first();

        if ($comment) {
            $comment->delete();
            Cache::forget('recent_comments');
            return $this->returnSuccessMessage('Comment deleted successfully', 200);
        }
        return $this->returnError('E001', 'Something was wrong');
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->returnSuccessMessage(' logout successfully', 200);
    }

}
