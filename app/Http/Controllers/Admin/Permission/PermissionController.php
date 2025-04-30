<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Permission\StorePermissionRequest;
use App\Http\Requests\Admin\Permission\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Permission::query();

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $permissions = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
            $roles = Role::orderBy('name')->get();

            return view('admin.permissions.index', compact('permissions', 'roles'));
        } catch (\Exception $e) {
            Log::error('Error fetching permissions: ' . $e->getMessage());
            abort(500, 'Terjadi kesalahan');
        }
    }

    public function store(StorePermissionRequest $request)
    {
        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            if ($request->roles) {
                $permission->syncRoles($request->roles);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission berhasil dibuat.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating permission: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat permission.']);
        }
    }

    public function edit($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $roles = Role::orderBy('name')->get();
            $assignedRoles = $permission->roles->pluck('id')->toArray();

            return response()->json([
                'permission' => $permission,
                'assigned_roles' => $assignedRoles,
                'roles' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching permission for edit: ' . $e->getMessage());
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }
    }

    public function update(UpdatePermissionRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $permission = Permission::findOrFail($id);
            $permission->update([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            if ($request->roles) {
                $permission->syncRoles($request->roles);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permission berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating permission: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui permission.']);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();
            return response()->json(['success' => true, 'message' => 'Permission berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error deleting permission: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus permission.']);
        }
    }
}
