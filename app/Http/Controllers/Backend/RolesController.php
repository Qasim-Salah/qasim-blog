<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolesRequest;
use Illuminate\Http\Request;
use App\Models\Role;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::get(); // use pagination and  add custom pagination on index.blade
        return view('backend.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('backend.roles.create');
    }

    public function store(RolesRequest $request)
    {

        $role = $this->process(new Role, $request);
        if ($role)
            return redirect()->route('admin.roles.index')->with(['success' => 'تم ألاضافة بنجاح']);
        return redirect()->route('admin.roles.index')->with(['error' => 'رساله الخطا']);

    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('backend.roles.edit', compact('role'));
    }

    public function update($id, RolesRequest $request)
    {
        $role = Role::findOrFail($id);
        $role = $this->process($role, $request);
        if ($role)
            return redirect()->route('admin.roles.index')->with(['success' => 'تم التحديث بنجاح']);
        return redirect()->route('admin.roles.index')->with(['error' => 'رساله الخطا']);

    }

    protected function process(Role $role, Request $r)
    {
        $role->name = $r->name;
        $role->permissions = json_encode($r->permissions);
        $role->save();
        return $role;
    }


}
