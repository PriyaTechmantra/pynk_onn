<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Auth;
class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view role', ['only' => ['index']]);
        $this->middleware('permission:create role', ['only' => ['create','store','addPermissionToRole','givePermissionToRole']]);

        $this->middleware('permission:update role', ['only' => ['update','edit']]);

        $this->middleware('permission:delete role', ['only' => ['destroy']]);
    }

    public function index()
    {
        $roles = Role::where('is_deleted',0)->paginate(25);
        return view('role-permission.role.index', ['roles' => $roles]);
    }

    public function create()
    {
        return view('role-permission.role.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name'
            ]
        ]);

        $role=Role::create([
            'name' => $request->name
        ]);
        
        DB::table('edit_logs')->insert([
            'table_name' => 'roles',
            'record_id'  => $role->id,
            'action'     => 'created',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);
        return redirect('roles/'.$role->id.'/give-permissions')->with('success','Role Created Successfully');
    }

    public function edit(Role $role)
    {
        return view('role-permission.role.edit',[
            'role' => $role
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name,'.$role->id
            ]
        ]);
        $oldUserData = $role->replicate();
        $role->update([
            'name' => $request->name
        ]);

         // 🔹 Log user table changes
        $changedUserFields = $role->getChanges();

        foreach ($changedUserFields as $field => $newValue) {
            if (in_array($field, ['updated_at'])) continue;

            $oldValue = $oldUserData->$field ?? null;

            DB::table('edit_logs')->insert([
                'table_name' => 'roles',
                'record_id' => $role->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action' => 'updated',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }
        return redirect('roles')->with('success','Role Updated Successfully');
    }

    public function destroy($roleId)
    {
        $isEReferenced = DB::table('model_has_roles')->where('role_id', $roleId)->exists();
        if ($isEReferenced) {
            return redirect()->back()->with('failure', 'Role cannot be deleted because it is referenced in another table.');
        }
        $role = Role::find($roleId);
        $role->is_deleted=1;
        $role->save();

        // ✅ Log the delete action only (no old/new value)
        DB::table('edit_logs')->insert([
            'table_name' => 'roles',
            'record_id' => $role->id,
            'action' => 'deleted',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);
        return redirect('roles')->with('success','Role Deleted Successfully');
    }

    public function addPermissionToRole($roleId)
    {
        $permissions = Permission::get()->groupBy('category');
        $role = Role::findOrFail($roleId);
        $rolePermissions = DB::table('role_has_permissions')
                                ->where('role_has_permissions.role_id', $role->id)
                                ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
                                ->all();

        return view('role-permission.role.add-permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function givePermissionToRole(Request $request, $roleId)
    {
        $request->validate([
            'permission' => 'required'
        ]);

        $role = Role::findOrFail($roleId);
        // 🔹 Get old permissions before sync
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $role->syncPermissions($request->permission);

         // 🔹 Get new permissions after sync
        $newPermissions = $role->permissions->pluck('name')->toArray();


        // 🔹 Find differences for logging
        $addedPermissions = array_diff($newPermissions, $oldPermissions);
        $removedPermissions = array_diff($oldPermissions, $newPermissions);

        // 🔹 Log added permissions
        foreach ($addedPermissions as $perm) {
            DB::table('edit_logs')->insert([
                'table_name' => 'role_has_permissions',
                'record_id' => $role->id,
                'field' => 'permission',
                'old_value' => null,
                'new_value' => $perm,
                'action' => 'permission_added',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }

        // 🔹 Log removed permissions
        foreach ($removedPermissions as $perm) {
            DB::table('edit_logs')->insert([
                'table_name' => 'role_has_permissions',
                'record_id' => $role->id,
                'field' => 'permission',
                'old_value' => $perm,
                'new_value' => null,
                'action' => 'permission_removed',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }
        return redirect()->back()->with('success','Permissions added to role');
    }
}
