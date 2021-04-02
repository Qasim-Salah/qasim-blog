<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use App\Http\Requests\Frontend\CommentRequest;
use App\Http\Requests\Frontend\ContactRequest;
use App\Models\Category as CategoryModel;
use App\Models\Contact as ContactModel;
use App\Models\Post as PostModel;
use App\Models\User as UserModel;
use App\Notifications\NewCommentForAdminNotify;
use App\Notifications\NewCommentForPostOwner;
use Illuminate\Http\Request;
use Stevebauman\Purify\Purify;


class IndexController extends Controller
{
    public function index()
    {
        $posts = PostModel::active()->with(['user'])
            ->whereHas('category', function ($q) {
                $q->active();
            })->
            whereHas('user', function ($q) {
                $q->active();
            })->
            post()->orderBy('id', 'desc')->paginate(PAGINATION_COUNT);

        return view('frontend.index', compact('posts'));
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

            return view('frontend.index', compact('posts'));
        }

    }

    public function category($category_slug)
    {
        $category = CategoryModel::active()->where('slug', $category_slug)->orWhere('id', $category_slug)->first()->id;

        if ($category) {
            $posts = PostModel::with(['user'])
                ->where('category_id', $category)
                ->post()
                ->active()
                ->paginate(PAGINATION_COUNT);

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('Frontend.index');

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
        return view('frontend.index', compact('posts'));

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

            return view('frontend.index', compact('posts'));
        }

        return redirect()->route('Frontend.index');
    }

    public function post_show($post_slug)
    {
        $post = PostModel::active()->where('slug', $post_slug)->with
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
            return redirect()->route('Frontend.index');

        $blade = $post->post_type == 'post' ? 'post' : 'page';

        if ($blade)
            return view('Frontend.' . $blade, compact('post'));

        return redirect()->route('Frontend.index');

    }

    public function store_comment(CommentRequest $request, $slug)
    {
        $post = PostModel::where('slug', $slug)->post()->first();
        if ($post) {

            $userId = auth()->check() ? auth()->id() : null;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['url'] = $request->url;
            $data['ip_address'] = $request->ip();
            $data['comment'] = Purify::clean($request->comment);
            $data['post_id'] = $post->id;
            $data['user_id'] = $userId;

            /* $comment=*/
            $post->comments()->create($data);
//
//            if (auth()->guest() || auth()->id() != $post->user_id) {
//                $post->user->notify(new NewCommentForPostOwner($comment));
//        }

//            User::whereHas('roles', function ($query) {
//                $query->whereIn('name', ['admin']);
//            })->each(function ($admin, $key) use ($comment) {
//                $admin->notify(new NewCommentForAdminNotify($comment));
//            });

            return redirect()->back()->with([
                'message' => 'Comment added successfully',
                'alert-type' => 'success']);
        }

        return redirect()->back()->with(['message' => 'Something was wrong',
            'alert-type' => 'danger']);

    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function do_contact(ContactRequest $request)
    {
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['title'] = $request->title;
        $data['message'] = $request->message;

        if (ContactModel::create($data))
            return redirect()->back()->with(['message' => 'Message sent successfully', 'alert-type' => 'success']);

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);


    }


}
