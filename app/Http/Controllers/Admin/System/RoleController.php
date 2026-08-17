<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\System\Permission;
use App\Models\System\Role;
use Illuminate\Validation\Rule;
use App\Models\System\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*  $query = Role::where('id', operator: '!=', 2)->latest();
        $query->whereLike($request->field_name, $request->value); */

        $query = Role::where('id', '!=', 2)->latest();
        $query->whereLike($request->field_name, $request->value);

        if ($request->allData) {
            return $query->select('id', 'name')->get();
        } else {
            $datas = $query->paginate($request->pagination);

            return new Resource($datas);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('layouts.admin_app');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if (Role::where('name', $request->name)->exists()) {
            return back()
                ->withErrors([
                    'name' => 'The role name has already been taken.',
                ])
                ->withInput();
        }
        $data = $request->all();

        $permissions = $request->get('permissions');
        unset($data['permissions']);

        $Role = Role::create($data);

        if ($Role) {
            if ($permissions && count($permissions)) {
                $rolePermissions = $Role->permissions()->sync($permissions);
                if ($rolePermissions) {
                    Cache::forget('side_menu_cache');
                    Cache::forget('role_pemission_cache');

                    return response()->json(['message' => 'You have successfully created'], 200);
                } else {
                    return response()->json(['message' => 'You have successfully created role but no permission set'], 200);
                }
            } else {
                return response()->json(['message' => 'You have successfully created role but no permission set'], 200);
            }
        } else {
            return response()->json(['error' => 'Opps! Something wrong. Please try again.'], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->format() == 'html') {
            return view('layouts.admin_app');
        }
        $data = Role::find($id);
        $data['permissions'] = $this->getAccessPermissions($data->id);

        return $data;
    }

    // GET PERMISSIONS
    public function getPermissions()
    {
        /*create permission*/
        $this->createRolePermission();

        return Permission::with('children')->whereNull('parent_id')->get();
    }

    // GET ACCESS PERMISSIONS
    public function getAccessPermissions($id)
    {
        return RolePermission::where('role_id', $id)
            ->pluck('permission_id')->toArray();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('layouts.admin_app');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('roles', 'name')->ignore($id),
            ],
            'status' => 'required',
        ]);

        $data = $request->only([
            'name',
            'status',
        ]);

        $role->update($data);

        $role->permissions()->sync($request->permissions ?? []);

        Cache::forget('role_pemission_cache');

        return response()->json([
            'message' => 'You have successfully updated'
        ], 200);
    }

    // SYSTEM ROLE UPDATE
    public function systemsRoleUpdate(Request $request, $roleId = null)
    {
        Cache::forget('role_pemission_cache');
        $this->createRolePermission();

        /*===Administrator===*/
        $permissions = Permission::pluck('id');
        $role = Role::find($roleId ?? 1);
        $role->permissions()->sync($permissions);

        $this->ownFunctionCall();

        if ($request->format() == 'html') {
            session()->flash('successMessage', 'Update system role');
            return back();
        }

        return response()->json(['message' => 'Update system role']);
    }

    public function ownFunctionCall()
    {
        Cache::forget('role_pemission_cache');
        /*===Administrator===*/
        $permissions = Permission::pluck('id');
        $role = Role::find(2);
        $role->permissions()->sync($permissions);
    }

    // PERMISSION CREATE FOR ROLE
    private function createRolePermission()
    {
        $allMenuListInserted = App::make('permittedMenuArr');
        $allRoutes = Route::getRoutes();
        $controllers = [];
        foreach ($allRoutes as $route) {
            $action = $route->getAction();

            if (!empty($action['middleware'])) {

                if (is_array($action['middleware']) && in_array('auth.access', $action['middleware']) && !empty($action['as'])) {
                    $route = explode('.', $action['as']);
                    $controllerActionName = class_basename($action['controller'] ?? '');

                    if (!empty($controllerActionName) && !in_array($controllerActionName, $allMenuListInserted)) {
                        $controllerAction = explode('@', $controllerActionName);
                        if (isset($controllerAction[0]) && isset($controllerAction[1])) {
                            $controllers[$controllerAction[0]][$controllerAction[1]] = $route[0];
                        }
                    }
                }
            }
        }

        foreach ($controllers as $key => $controller) {
            $data['name'] = $key;
            $parent = Permission::firstOrCreate($data);
            if ($parent) {
                $data2['parent_id'] = $parent->id;
                foreach ($controller as $process => $route) {
                    $data2['name'] = $process;
                    $data2['route'] = $route . '.' . $process;
                    Permission::firstOrCreate($data2);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        RolePermission::where('role_id', $role->id)->delete();
        Cache::forget('role_pemission_cache');
        if ($role->delete()) {
            return response()->json(['message' => 'Delete Successfully!'], 200);
        } else {
            return response()->json(['error' => 'Delete Unsuccessfully!'], 200);
        }
    }
}
