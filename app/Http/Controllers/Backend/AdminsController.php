<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\Admin as AdminModel;
use App\Models\Role as RoleModel;


class AdminsController extends Controller
{

    public function index()
    {
        $users = AdminModel::latest()->where('id', '<>', auth()->id())->get(); //use pagination here
        return view('backend.admins.index', compact('users'));
    }

    public function create()
    {
        $roles = RoleModel::get();
        return view('backend.admins.create', compact('roles'));
    }


    public function store(AdminRequest $request)
    {

        $user = AdminModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'status'=>$request->status
        ]);
        // save the new user data
        if ($user)
            return redirect()->route('admin.admin.index')->with(['success' => 'تم التحديث بنجاح']);
        return redirect()->route('admin.admin.index')->with(['success' => 'حدث خطا ما']);

    }
}
