<?php

namespace App\Http\Controllers;

use App\Exports\UserBulkTemplateExport;
use App\Imports\UserBulkRowsImport;
use App\Models\User;
use App\Services\UserBulkImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ManageUserController extends Controller
{
    private const ROLES = ['admin', 'ppic', 'prod', 'mh', 'injection', 'direct'];

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $role = trim((string) $request->input('role', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('npk', 'like', "%{$q}%");
                });
            })
            ->when($role !== '' && in_array($role, array_merge(self::ROLES, ['super_admin']), true), fn ($query) => $query->where('role', $role))
            ->orderByRaw("role = 'super_admin' DESC")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $editUser = null;
        if ($request->filled('edit')) {
            $editUser = User::find($request->input('edit'));
            if ($editUser && $editUser->isSuperAdmin()) {
                $editUser = null;
            }
        }

        return view('pages.manage_users', [
            'users' => $users,
            'roles' => self::ROLES,
            'roleFilterOptions' => array_merge(['super_admin'], self::ROLES),
            'editUser' => $editUser,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'npk' => $validated['npk'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('manage-users.index')->with('manage_user_success', 'User baru berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('manage_user_error', 'Akun super admin tidak bisa diubah dari Manage User.');
        }

        $validated = $request->validate($this->rules($user));

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'npk' => $validated['npk'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('manage-users.index')->with('manage_user_success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('manage_user_error', 'Akun sendiri tidak boleh dihapus.');
        }

        if ($user->isSuperAdmin()) {
            return back()->with('manage_user_error', 'Akun super admin tidak boleh dihapus.');
        }

        $user->delete();

        return back()->with('manage_user_success', 'User berhasil dihapus.');
    }

    public function template()
    {
        return Excel::download(new UserBulkTemplateExport(self::ROLES), 'template_bulk_users.xlsx');
    }

    public function bulkUpload(Request $request, UserBulkImporter $importer)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        try {
            $sheets = Excel::toArray(new UserBulkRowsImport, $validated['file']);
            $result = $importer->process($sheets[0] ?? []);
        } catch (\Throwable $e) {
            return back()->with('manage_user_error', 'Bulk upload gagal: '.$e->getMessage());
        }

        $flash = $result['imported'] > 0 ? 'manage_user_success' : 'manage_user_error';
        $message = "Bulk upload selesai. User berhasil dibuat: {$result['imported']}. Error: ".count($result['errors']).'.';

        return back()
            ->with($flash, $message)
            ->with('user_import_result', $result);
    }

    private function rules(?User $user = null): array
    {
        $ignoreId = $user?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'npk' => ['required', 'digits:6', Rule::unique('users', 'npk')->ignore($ignoreId)],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];
    }
}
