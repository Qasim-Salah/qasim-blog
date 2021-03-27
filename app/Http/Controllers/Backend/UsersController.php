<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Scopes\GlobalScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UsersController extends Controller
{

    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $users = User::withoutGlobalScope(GlobalScope::class)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            });
        if ($keyword != null) {
            $users = $users->where('name', 'LIKE', '%' . $keyword . '%')
                ->orwhere('email', 'LIKE', '%' . $keyword . '%')
                ->orwhere('mobile', 'LIKE', '%' . $keyword . '%');
        }
        if ($status != null) {
            $users = $users->whereStatus($status);
        }

        $users = $users->orderBy($sort_by, $order_by);
        $users = $users->paginate($limit_by);

        return view('backend.users.index', compact('users'));
    }

    public function create()
    {
        return view('backend.users.create');
    }

    public function store(UserRequest $request)
    {
        try {

            $data['name'] = $request->name;
            $data['username'] = $request->username;
            $data['email'] = $request->email;
            $data['email_verified_at'] = Carbon::now();
            $data['mobile'] = $request->mobile;
            $data['password'] = bcrypt($request->password);
            $data['status'] = $request->status;
            $data['role_id'] = 3;
            $data['bio'] = $request->bio;
            $data['receive_email'] = $request->receive_email;
            DB::beginTransaction();
            if ($user_image = $request->file('user_image')) {
                $filename = Str::slug($request->username) . '.' . $user_image->getClientOriginalExtension();
                $path = public_path('assets/users/' . $filename);
                Image::make($user_image->getRealPath())->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($path, 100);
                $data['user_image'] = $filename;
            }

            $user = User::create($data);

            DB::commit();

            return redirect()->route('admin.users.index')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        } catch (\Exception $ex) {

            DB::rollback();
            return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);
        }
    }

    public function show($id)
    {
        try {
            $user = User::withoutGlobalScope(GlobalScope::class)->where('id', $id)->withCount('posts')->first();
            if ($user) {
                return view('backend.users.show', compact('user'));
            }
        } catch (\Exception $ex) {
            return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function edit($id)
    {

        try {
            $user = User::withoutGlobalScope(GlobalScope::class)->where('id', $id)->withCount('posts')->first();
            if ($user) {
                return view('backend.users.edit', compact('user'));
            }
        } catch (\Exception $ex) {

            return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function update(UserRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = User::withoutGlobalScope(GlobalScope::class)->where('id', $id)->first();

            if ($user) {
                $data['name'] = $request->name;
                $data['username'] = $request->username;
                $data['email'] = $request->email;
                $data['mobile'] = $request->mobile;
                if (trim($request->password) != '') {
                    $data['password'] = bcrypt($request->password);
                }
                $data['status'] = $request->status;
                $data['bio'] = $request->bio;
                $data['receive_email'] = $request->receive_email;

                if ($user_image = $request->file('user_image')) {
                    if ($user->user_image != '') {
                        if (File::exists('assets/users/' . $user->user_image)) {
                            unlink('assets/users/' . $user->user_image);
                        }
                    }
                    $filename = Str::slug($request->username) . '.' . $user_image->getClientOriginalExtension();
                    $path = public_path('assets/users/' . $filename);
                    Image::make($user_image->getRealPath())->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);
                    $data['user_image'] = $filename;
                }

                $user->update($data);
                DB::commit();
                return redirect()->route('admin.users.index')->with(['message' => 'User updated successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function destroy($id)
    {
        try {

            $user = User::where('id', $id)->first();

            if ($user) {
                if ($user->user_image != '') {
                    if (File::exists('assets/users/' . $user->user_image)) {
                        unlink('assets/users/' . $user->user_image);
                    }
                }
                $user->delete();

                return redirect()->route('admin.users.index')->with(['message' => 'User deleted successfully', 'alert-type' => 'success',]);
            }
        } catch (\Exception $ex) {

            return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
        }
    }

    public function removeImage(Request $request)
    {
        try {

            $user = User::where('id', $request->user_id)->first();
            if ($user) {
                if (File::exists('assets/users/' . $user->user_image)) {
                    unlink('assets/users/' . $user->user_image);
                }
                $user->user_image = null;
                $user->save();
                return 'true';
            }
        } catch (\Exception $ex) {
            return 'false';
        }
    }
}
