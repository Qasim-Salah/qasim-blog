<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Story_PageRequest;
use App\Models\Category as CategoryModel;
use App\Models\Page as PageModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Stevebauman\Purify\Facades\Purify;

class PagesController extends Controller
{
    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $categoryId = !empty(\request()->category_id) ? \request()->category_id : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $pages = PageModel::page();


        if (!empty($keyword)) {
            $pages = $pages->where('title', 'LIKE', '%' . $keyword . '%');
        }
        if (!empty($categoryId)) {
            $pages = $pages->where('category_id', $categoryId);
        }
        if (!empty($status)) {
            $pages = $pages->where('status', $status);
        }

        $pages = $pages->orderBy($sort_by, $order_by);
        $pages = $pages->paginate($limit_by);

        $categories = CategoryModel::pluck('name', 'id');
        return view('backend.pages.index', compact('categories', 'pages'));

    }

    public function create()
    {

        $categories = CategoryModel::pluck('name', 'id');
        return view('backend.pages.create', compact('categories'));
    }

    public function store(Story_PageRequest $request)
    {
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }

        $data['title'] = Purify::clean($request->title);
        $data['description'] = Purify::clean($request->description);
        $data['status'] = $request->status;
        $data['post_type'] = 'page';
        $data['comment_able'] = 0;
        $data['category_id'] = $request->category_id;
        $data['image'] = $fileName;

        $page = auth()->user()->posts()->create($data);

        if ($page)
            return redirect()->route('admin.pages.index')->with(['message' => 'Page created successfully', 'alert-type' => 'success']);

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function show($id)
    {
        $page = PageModel::findOrfail($id);
        return view('backend.pages.show', compact('page'));
    }

    public function edit($id)
    {

        $categories = CategoryModel::pluck('name', 'id');
        $page = PageModel::findOrfail($id);

        return view('backend.pages.edit', compact('categories', 'page'));
    }

    public function update(Story_PageRequest $request, $id)
    {
            $page = PageModel::findOrfail($id);
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('posts', $request->image);
        }

            if ($page) {
                $data['title'] = $request->title;
                $data['slug'] = null;
                $data['description'] = Purify::clean($request->description);
                $data['status'] = $request->status;
                $data['category_id'] = $request->category_id;
                $data['image'] = $fileName;

                $page->update($data);

                return redirect()->route('admin.pages.index')->with(['message' => 'Page updated successfully', 'alert-type' => 'success',]);
            }

            return redirect()->route('admin.pages.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

    public function destroy($id)
    {
            $page = PageModel::findOrfail($id)->page();

            if ($page) {
                $page->delete();
                return redirect()->route('admin.pages.index')->with(['message' => 'Page deleted successfully', 'alert-type' => 'success',]);

            }

            return redirect()->route('admin.pages.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

}
