<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use  Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use DB;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view user', ['only' => ['index']]);
        $this->middleware('permission:create user', ['only' => ['create','store']]);
        $this->middleware('permission:update user', ['only' => ['update','edit']]);
        $this->middleware('permission:delete user', ['only' => ['destroy']]);
    }

    public function index()
    {
        $users = User::paginate(25);
        
        return view('role-permission.user.index', ['users' => $users]);
    }

    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        
       
        return view('role-permission.user.create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:20',
            'roles' => 'required'
        ]);

        $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                    ]);

        $user->syncRoles($request->roles);
        $user_id = 0;
        DB::table('user_permission_categories')->updateOrInsert(
                    ['user_id' => $user->id, 'brand' => $request->brand],
                    
                    ['created_at' => now(), 'updated_at' => now()]
                    );

                     // ✅ Log the create action (no old/new values)
        DB::table('edit_logs')->insert([
            'table_name' => 'users',
            'record_id'  => $user->id,
            'action'     => 'created',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect('/users')->with('success','User created successfully with roles');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name','name')->all();
        $userRoles = $user->roles->pluck('name','name')->all();
        return view('role-permission.user.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }

    public function update(Request $request, User $user)
    {
       
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|max:20',
            'roles' => 'required'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if(!empty($request->password)){
            $data += [
                'password' => Hash::make($request->password),
            ];
        }

        $user->update($data);
        $user->syncRoles($request->roles);
        $existing = DB::table('user_permission_categories')->where('user_id', $user->id)->first();

        if ($existing) {
            DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->update([
                    'brand' => $request->brand,
                    'updated_at' => now()
                ]);
        } else {
            DB::table('user_permission_categories')->insert([
                'user_id' => $user->id,
                'brand' => $request->brand,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $oldData = $user->replicate();
        // ✅ Compare old vs new and log only changed fields
        $changedFields = $user->getChanges(); // only changed attributes

        foreach ($changedFields as $field => $newValue) {
            if (in_array($field, ['updated_at'])) continue; // skip timestamps

            $oldValue = $oldData->$field ?? null;

            DB::table('edit_logs')->insert([
                'table_name' => 'users',
                'record_id' => $user->id,
                'field' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action' => 'updated',
                'updated_by' => Auth::id(),
                'created_at' => now(),
            ]);
        }
        return redirect('/users')->with('success','User Updated Successfully with roles');
    }

    public function destroy($userId)
    {
        $isDReferenced = DB::table('distributors')->where('user_id', $userId)->exists();
        $isEReferenced = DB::table('employees')->where('created_by', $userId)->exists();
        if ($isDReferenced || $isEReferenced) {
            return redirect()->back()->with('failure', 'User cannot be deleted because it is referenced in another table.');
        }
        $user = User::findOrFail($userId);
        $user->is_deleted=1;
        $user->delete();
        

        // ✅ Log the delete action only (no old/new value)
        DB::table('edit_logs')->insert([
            'table_name' => 'users',
            'record_id' => $user->id,
            'action' => 'deleted',
            'updated_by' => Auth::id(),
            'created_at' => now(),
        ]);
        return redirect('/users')->with('success','User Delete Successfully');
    }
}