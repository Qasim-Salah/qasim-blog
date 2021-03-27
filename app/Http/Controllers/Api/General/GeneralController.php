<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\General\PostResource;
use App\Http\Resources\General\PostsResource;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewCommentForAdminNotify;
use App\Notifications\NewCommentForPostOwner;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    use GeneralTrait;

    public function get_posts()
    {
        $posts = Post::whereHas('category', function ($query) {
            $query->whereStatus(1);
        })
            ->whereHas('user', function ($query) {
                $query->whereStatus(1);
            })
            ->post()->orderBy('id', 'desc')->paginate(5);

        if ($posts->count() > 0) {
            return $this->returnData('get_posts',PostsResource::collection($posts));
        } else {
            return $this->returnError(201, 'No posts found');
        }


    }

    public function search(Request $request)
    {
        $keyword = !empty($request->keyword) ? $request->keyword : null;
        ##GlobalScope##
        $posts = Post::where('title', 'LIKE', '%' . $keyword . '%')->orWhere('description', 'LIKE', '%' . $keyword . '%')->with(['media', 'user']);
        $posts = $posts->post()->paginate(PAGINATION_COUNT);

        if ($posts->count() > 0) {
            return $this->returnData('search', PostsResource::collection($posts));
        } else {
            return $this->returnError('E001', 'No posts found');

        }
    }

    public function category($slug)
    {
        try {
            $category = Category::where('slug', $slug)->orWhere('id', $slug)->first()->id;

            if ($category) {
                $posts = Post::with(['media', 'user'])
                    ->where('category_id', $category)
                    ->post();

                return $this->returnData('category', PostsResource::collection($posts));
            }
        } catch (\Exception $ex) {

            return $this->returnError('E001', 'Something was wrong');
        }
    }

    public function archive($date)
    {
        $exploded_date = explode('-', $date);
        $month = $exploded_date[0];
        $year = $exploded_date[1];

        $posts = Post::with(['media', 'user'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->post()
            ->get();

        if ($posts->count() > 0) {
            return $this->returnData('archive', PostsResource::collection($posts));
        } else {
            return $this->returnError('E001', 'Something was wrong');
        }

    }

    public function author($username)
    {
        $user = User::whereUsername($username)->first();

        if ($user) {
            $posts = Post::with(['media', 'user'])
                ->whereUserId($user->id)
                ->post()
                ->get();

            if ($posts->count() > 0) {
                return $this->returnData('author', PostsResource::collection($posts));
            } else {
                return $this->returnError('E001', 'Something was wrong');
            }
        }
    }

    public function show_post($slug)
    {
        try {
            ##GlobalScope##
            $post = Post::with(['category', 'media', 'user', 'approved_comments']);

            $post = $post->where('slug', $slug)->post()->first();

            if ($post)
                return $this->returnData('show_post', new PostResource($post));

        } catch (\Exception $ex) {

            return $this->returnError(201, 'No posts found');
        }
    }

    public function store_comment(Request $request, $slug)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'url' => 'nullable|url',
            'comment' => 'required|min:10',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }

        $post = Post::where('slug', $slug)->post()->first();
        if ($post) {

            $userId = auth()->check() ? auth()->id() : null;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['url'] = $request->url;
            $data['ip_address'] = $request->ip();
            $data['comment'] = $request->comment;
            $data['post_id'] = $post->id;
            $data['user_id'] = $userId;

            $comment = $post->comments()->create($data);

            if (auth()->guest() || auth()->id() != $post->user_id) {
                $post->user->notify(new NewCommentForPostOwner($comment));
            }

            User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin']);
            })->each(function ($admin, $key) use ($comment) {
                $admin->notify(new NewCommentForAdminNotify($comment));
            });
            return $this->returnSuccessMessage('Comment added successfully', 200);
        }
        return $this->returnError(201, 'Something was wrong');

    }

    public function do_contact(Request $request)
    {
         $rules=[
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'nullable|numeric',
            'title' => 'required|min:5',
            'message' => 'required|min:10',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['title'] = $request->title;
        $data['message'] = $request->message;

        Contact::create($data);
        return $this->returnSuccessMessage('Message sent successfully', 200);

    }

}
