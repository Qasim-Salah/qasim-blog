<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\categoryRequest;
use App\Models\Category as CategoryModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;


class PostCategoriesController extends Controller
{
    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $categories = CategoryModel::withCount('posts');

        if (!empty($keyword)) {
            $categories = $categories->where('name', 'LIKE', '%' . $keyword . '%');
        }
        if (!empty($status)) {
            $categories = $categories->where('status', $status);
        }

        $categories = $categories->orderBy($sort_by, $order_by);
        $categories = $categories->paginate($limit_by);

        return view('backend.post_categories.index', compact('categories'));

    }

    public function create()
    {
        return view('backend.post_categories.create');
    }

    public function store(categoryRequest $request)
    {

        $category = CategoryModel::create($request->all());

        if ($request->status == 1) {
            Cache::forget('recent_posts');

        }
        if ($category)
            return redirect()->route('admin.post_categories.index')->with(['message' => 'category created successfully', 'alert-type' => 'success']);

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $category = CategoryModel::findOrfail($id);
        return view('backend.post_categories.edit', compact('category'));
    }

    public function update(categoryRequest $request, $id)
    {
        $category = CategoryModel::findOrfail($id);

        if ($category) {
            $data['name'] = $request->name;
            $data['slug'] = null;
            $data['status'] = $request->status;

            $category->update($data);
            Cache::forget('recent_posts');

            return redirect()->route('admin.post_categories.index')->with(['message' => 'category updated successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.post_categories.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function destroy($id)
    {

        $category = CategoryModel::findOrfail($id);
        if ($category) {
            $category->delete();
            $category->posts()->delete();

            return redirect()->route('admin.post_categories.index')->with(['message' => 'category deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.post_categories.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }
}


