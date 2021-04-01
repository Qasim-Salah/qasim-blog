<?php

namespace App\Providers;

use App\Models\Category as CategoryModel;
use App\Models\Comment as CommentModel;
use App\Models\Post as PostModel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (!request()->is('admin/*')) {
            Paginator::defaultView('vendor.pagination.boighor');
            view()->composer('*', function ($view) {

                if (!Cache::has('recent_posts')) {
                    $recent_posts = PostModel::with(['user', 'category'])
                        ->whereHas('category', function ($q) {
                            $q->active();
                        })->
                        whereHas('user', function ($q) {
                            $q->active();
                        })->
                        post()->orderBy('id', 'desc')->limit(5)->get();

                    Cache::remember('recent_posts', 3600, function () use ($recent_posts) {
                        return $recent_posts;
                    });
                }
                $recent_posts = Cache::get('recent_posts');


                if (!Cache::has('recent_comments')) {
                    $recent_comments = CommentModel::active()->orderBy('id', 'desc')->limit(5)->get();

                    Cache::remember('recent_comments', 3600, function () use ($recent_comments) {
                        return $recent_comments;
                    });
                }
                $recent_comments = Cache::get('recent_comments');


                if (!Cache::has('global_categories')) {
                    $global_categories = CategoryModel::Active()->orderBy('id', 'desc')->get();

                    Cache::remember('global_categories', 3600, function () use ($global_categories) {
                        return $global_categories;
                    });
                }
                $global_categories = Cache::get('global_categories');


                if (!Cache::has('global_archives')) {
                    $global_archives = PostModel::active()->orderBy('created_at', 'desc')
                        ->select(DB::raw("Year(created_at) as year"), DB::raw("Month(created_at) as month"))
                        ->pluck('year', 'month')->toArray();

                    Cache::remember('global_archives', 3600, function () use ($global_archives) {
                        return $global_archives;
                    });
                }
                $global_archives = Cache::get('global_archives');


                $view->with([
                    'recent_posts' => $recent_posts,
                    'recent_comments' => $recent_comments,
                    'global_categories' => $global_categories,
                    'global_archives' => $global_archives,
                ]);

            });

        }
    }
}
