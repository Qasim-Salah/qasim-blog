<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\General\PostResource;
use App\Http\Resources\General\PostsResource;
use App\Models\Category as CategoryModel;
use App\Models\Contact as ContactModel;
use App\Models\Post as PostModel;
use App\Models\User as UserModel;
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
        $posts = PostModel::whereHas('category', function ($query) {
            $query->active();
        })
            ->whereHas('user', function ($query) {
                $query->active();
            })
            ->post()->active()->latest()->paginate(PAGINATION_COUNT);

        if ($posts)
            return $this->returnData('get_posts', PostsResource::collection($posts));
        return $this->returnError(201, 'No posts found');

    }

    public function search(Request $request)
    {

        $keyword = ($request->keyword);
        if ($keyword) {
            $posts = PostModel::active()->with(['user' => function ($q) {
                $q->active();
            }])
                ->where('title', 'LIKE', '%' . $keyword . '%')
                ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            $posts = $posts->post()->paginate(PAGINATION_COUNT);

            return $this->returnData('search', PostsResource::collection($posts));
        }
    }

    public function category($slug)
    {
        $category = CategoryModel::active()->where('slug', $slug)->orWhere('id', $slug)->first()->id;

        if ($category) {
            $posts = PostModel::with(['user'])
                ->where('category_id', $category)
                ->post()
                ->paginate(PAGINATION_COUNT);

            return $this->returnData('category', PostsResource::collection($posts));
        }
        return $this->returnError('E001', 'Something was wrong');
    }

    public function archive($date)
    {
        $exploded_date = explode('-', $date);
        $month = $exploded_date[0];
        $year = $exploded_date[1];

        $posts = PostModel::with(['user'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->post()
            ->active()
            ->get();

        if ($posts)
            return $this->returnData('archive', PostsResource::collection($posts));
        return $this->returnError('E001', 'Something was wrong');

    }

    public function author($username)
    {
        $user = UserModel::active()->where('name', $username)->first()->id;

        if ($user) {
            $posts = PostModel::with(['user'])
                ->where('user_id', $user)
                ->post()
                ->active()
                ->get();

            return $this->returnData('author', PostsResource::collection($posts));
        }
        return $this->returnError('E001', 'Something was wrong');

    }

    public function show_post($slug)
    {
        $post = PostModel::active()->where('slug', $slug)->with
        (['category' => function ($q) {
            $q->active();
        },
            'user' => function ($q) {
                $q->active();
            },
            'comments' => function ($q) {
                $q->active()->orderBy('id', 'desc');
            }])->first();

        if (!$post)
            return $this->returnData('show_post', new PostResource($post));
        return $this->returnError(201, 'No posts found');

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

        $post = PostModel::where('slug', $slug)->post()->first();
        if ($post) {

            $userId = auth()->check() ? auth()->id() : null;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['url'] = $request->url;
            $data['ip_address'] = $request->ip();
            $data['comment'] = $request->comment;
            $data['post_id'] = $post->id;
            $data['user_id'] = $userId;

            $post->comments()->create($data);


            return $this->returnSuccessMessage('Comment added successfully', 200);
        }
        return $this->returnError(201, 'Something was wrong');
    }

    public function do_contact(Request $request)
    {
        $rules = [
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

        ContactModel::create($data);
        return $this->returnSuccessMessage('Message sent successfully', 200);

    }

}
