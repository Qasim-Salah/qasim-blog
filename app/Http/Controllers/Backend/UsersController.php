<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User as UserModel;
use Carbon\Carbon;

class UsersController extends Controller
{

    public function index()
    {
        $keyword = !empty(\request()->keyword) ? \request()->keyword : null;
        $status = !empty(\request()->status) ? \request()->status : null;
        $sort_by = !empty(\request()->sort_by) ? \request()->sort_by : 'id';
        $order_by = !empty(\request()->order_by) ? \request()->order_by : 'desc';
        $limit_by = !empty(\request()->limit_by) ? \request()->limit_by : '10';

        $users = UserModel::latest();
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
        $fileName = "";
        if ($request->has('image')) {
            ###helper###
            $fileName = uploadImage('register', $request->image);
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['email_verified_at'] = Carbon::now();
        $data['mobile'] = $request->mobile;
        $data['password'] = bcrypt($request->password);
        $data['status'] = $request->status;
        $data['role_id'] = 3;
        $data['image'] = $fileName;
        $data['bio'] = $request->bio;
        $data['receive_email'] = $request->receive_email;
        $user = UserModel::create($data);

        if ($user)
            return redirect()->route('admin.users.index')->with(['message' => 'Post created successfully', 'alert-type' => 'success']);

        return redirect()->back()->with(['message' => 'Something was wrong', 'alert-type' => 'danger']);

    }

    public function show($id)
    {
        $user = UserModel::withCount('posts')->findOrfail($id);
        if ($user)

            return view('backend.users.show', compact('user'));

        return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function edit($id)
    {

        $user = UserModel::withCount('posts')->findOrfail($id);
        if ($user)
            return view('backend.users.edit', compact('user'));

        return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function update(UserRequest $request, $id)
    {
        $user = UserModel::findOrfail($id);

        $fileName = "";
        if ($request->has('register')) {
            ###helper###
            $fileName = uploadImage('users', $request->image);
        }

        if ($user) {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['mobile'] = $request->mobile;
            if (trim($request->password) != '') {
                $data['password'] = bcrypt($request->password);
            }
            $data['status'] = $request->status;
            $data['bio'] = $request->bio;
            $data['image'] = $fileName;
            $data['receive_email'] = $request->receive_email;
            $data['image'] = $fileName;

            $user->update($data);
            return redirect()->route('admin.users.index')->with(['message' => 'User updated successfully', 'alert-type' => 'success',]);
        }
        return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);

    }

    public function destroy($id)
    {

        $user = UserModel::findOrfail($id);

        if ($user) {
            $user->delete();

            return redirect()->route('admin.users.index')->with(['message' => 'User deleted successfully', 'alert-type' => 'success',]);
        }

        return redirect()->route('admin.users.index')->with(['message' => 'Something was wrong', 'alert-type' => 'danger',]);
    }

}
