<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;


use App\Http\Requests\CommentRequest;
use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Post as PostModel;
use App\Models\User;
use App\Notifications\NewCommentForAdminNotify;
use App\Notifications\NewCommentForPostOwner;
use App\Scopes\GlobalScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

//use Stevebauman\Purify\Facades\Purify;

class IndexController extends Controller
{
    public function index()
    {
        $posts = PostModel::with(['user', 'category'])
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })->
            whereHas('user', function ($q) {
                $q->where('status', 1);
            })->
            post()->orderBy('id', 'desc')->paginate(PAGINATION_COUNT);

        return view('frontend.index', compact('posts'));
    }

    public function search(Request $request)
    {

        $keyword = !empty($request->keyword) ? $request->keyword : null;
        ##GlobalScope##
        $posts = Post::where('title', 'LIKE', '%' . $keyword . '%')->orWhere('description', 'LIKE', '%' . $keyword . '%')->with(['media', 'user']);
        $posts = $posts->post()->paginate(PAGINATION_COUNT);

        return view('frontend.index', compact('posts'));
    }

    public function category($category_slug)
    {
        try {
            $category = Category::where('slug', $category_slug)->orWhere('id', $category_slug)->first()->id;

            if ($category) {
                $posts = Post::with(['media', 'user'])
                    ->where('category_id', $category)
                    ->post()
                    ->paginate(PAGINATION_COUNT);

                return view('frontend.index', compact('posts'));
            }
        } catch (\Exception $ex) {

            return redirect()->route('frontend.index');
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
            ->paginate(PAGINATION_COUNT);
        return view('frontend.index', compact('posts'));

    }

    public function author($username)
    {
        $user = User::where('username', $username)->first()->id;

        if ($user) {
            $posts = Post::with(['media', 'user'])
                ->where('user_id', $user)
                ->post()
                ->paginate(PAGINATION_COUNT);

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('frontend.index');
    }

    public function post_show($post_slug)
    {
        $post = PostModel::where('slug', $post_slug)->with(['category' => function ($q) {
            $q->active();
        },
            'user' => function ($q) {
                $q->active();
            },
            'comments' => function ($q) {
                $q->active()->orderBy('id', 'desc');
            }])->first();

        if (!$post)
            return redirect()->route('frontend.index');

        $blade = $post->post_type == 'post' ? 'post' : 'page';

        if ($blade)
            return view('frontend.' . $blade, compact('post'));

        return redirect()->route('frontend.index');

    }

    public function store_comment(CommentRequest $request, $slug)
    {
        try {
            ##GlobalScope##
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

                return redirect()->back()->with(['message' => 'Comment added successfully', 'alert-type' => 'success']);
            }
        } catch (\Exception $ex) {

            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function do_contact(ContactRequest $request)
    {
        try {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['mobile'] = $request->mobile;
            $data['title'] = $request->title;
            $data['message'] = $request->message;

            Contact::create($data);

            return redirect()->back()->with(['message' => 'Message sent successfully', 'alert-type' => 'success']);

        } catch (\Exception $ex) {

            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }

    }


}
