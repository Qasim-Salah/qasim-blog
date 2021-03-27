<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Story_PageRequest;
use App\Models\Category;
use App\Models\Page;
use App\Models\PostMedia;
use App\Scopes\GlobalScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
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

        $pages = Page::page();


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

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        return view('backend.pages.index', compact('categories', 'pages'));

    }

    public function create()
    {

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        return view('backend.pages.create', compact('categories'));
    }

    public function store(Story_PageRequest $request)
    {
        try {

            DB::beginTransaction();
            $data['title'] = Purify::clean($request->title);
            $data['description'] = Purify::clean($request->description);
            $data['status'] = $request->status;
            $data['post_type'] = 'page';
            $data['comment_able'] = 0;
            $data['category_id'] = $request->category_id;

            $page = auth()->user()->posts()->create($data);

            if ($request->images && count($request->images) > 0) {
                $i = 1;
                foreach ($request->images as $file) {
                    $filename = $page->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                    $file_size = $file->getSize();
                    $file_type = $file->getMimeType();
                    $path = public_path('assets/posts/' . $filename);
                    Image::make($file->getRealPath())->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);

                    $page->media()->create([
                        'file_name' => $filename,
                        'file_size' => $file_size,
                        'file_type' => $file_type,
                    ]);
                    $i++;
                }
            }

            DB::commit();

            return redirect()->route('admin.pages.index')->with(['message' => 'Page created successfully', 'alert-type' => 'success']);

        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function show($id)
    {

        $page = Page::with(['media'])->where('id', $id)->Page()->first();
        return view('backend.pages.show', compact('page'));
    }

    public function edit($id)
    {

        $categories = Category::withoutGlobalScope(GlobalScope::class)->pluck('name', 'id');
        $page = Page:: with(['media'])->where('id', $id)->Page()->first();

        return view('backend.pages.edit', compact('categories', 'page'));
    }

    public function update(Story_PageRequest $request, $id)
    {
        try {
            $page = Page:: withoutGlobalScope(GlobalScope::class)->where('id', $id)->Page()->first();

            if ($page) {
                $data['title'] = $request->title;
                $data['slug'] = null;
                $data['description'] = Purify::clean($request->description);
                $data['status'] = $request->status;
                $data['category_id'] = $request->category_id;
                DB::beginTransaction();
                $page->update($data);

                if ($request->images && count($request->images) > 0) {
                    $i = 1;
                    foreach ($request->images as $file) {
                        $filename = $page->slug . '-' . time() . '-' . $i . '.' . $file->getClientOriginalExtension();
                        $file_size = $file->getSize();
                        $file_type = $file->getMimeType();
                        $path = public_path('assets/posts/' . $filename);
                        Image::make($file->getRealPath())->resize(800, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($path, 100);

                        $page->media()->create([
                            'file_name' => $filename,
                            'file_size' => $file_size,
                            'file_type' => $file_type,
                        ]);
                        $i++;
                    }
                }
                DB::commit();
                return redirect()->route('admin.pages.index')->with(['message' => 'Page updated successfully', 'alert-type' => 'success',]);
            }

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->route('admin.pages.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function destroy($id)
    {
        try {
            $page = Page:: Where('id', $id)->page()->first();

            if ($page) {
                if ($page->media->count() > 0) {
                    foreach ($page->media as $media) {
                        if (File::exists('assets/posts/' . $media->file_name)) {
                            unlink('assets/posts/' . $media->file_name);
                        }
                    }
                }
                DB::beginTransaction();
                $page->media()->delete();
                $page->delete();
                DB::commit();

                return redirect()->route('admin.pages.index')->with(['message' => 'Page deleted successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->route('admin.pages.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function removeImage(Request $request)
    {

        $media = PostMedia::where('id', $request->media_id)->first();
        if ($media) {
            if (File::exists('assets/posts/' . $media->file_name)) {
                unlink('assets/posts/' . $media->file_name);
            }
            $media->delete();
            return true;
        }
        return false;
    }

}
