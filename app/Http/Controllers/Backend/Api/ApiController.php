<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{

    public function comments_chart()
    {
        $posts = PostModel::select(DB::raw('COUNT(*) as count'), DB::raw('Month(created_at) as month'))
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('Month(created_at)'))
            ->pluck('count', 'month');

        $comments = CommentModel::select(DB::raw('COUNT(*) as count'), DB::raw('Month(created_at) as month'))
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('Month(created_at)'))
            ->pluck('count', 'month');

        foreach ($comments->keys() as $month_number) {
            $labels[] = date('F', mktime(0, 0, 0, $month_number, 1));
        }

        $chart['labels'] = $labels;
        $chart['datasets'][0]['name'] = 'Comments';
        $chart['datasets'][0]['values'] = $comments->values()->toArray();

        $chart['datasets'][1]['name'] = 'Posts';
        $chart['datasets'][1]['values'] = $posts->values()->toArray();

        return response()->json($chart);
    }

    public function users_chart()
    {

        $users = UserModel::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(3)
            ->pluck('posts_count', 'name');


        $chart['labels'] = $users->keys()->toArray();
        $chart['datasets']['name'] = 'Top Users';
        $chart['datasets']['values'] = $users->values()->toArray();

        return response()->json($chart);
    }

}
