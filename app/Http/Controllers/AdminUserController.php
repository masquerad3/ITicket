<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = collect(DB::select('EXEC dbo.sp_read_all_users'));

        return view('pages.admin.users', compact('users'));
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:50'],
            'contact' => ['required', 'string', 'max:15'],
            'role' => ['required', 'string', 'in:user,it,admin'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Prevent an admin from accidentally removing their own admin access.
        if ((int) $user === (int) auth()->id() && $validated['role'] !== 'admin') {
            return redirect()
                ->route('admin.users.index')
                ->with('status', 'You cannot remove your own admin role.');
        }

        try {
            DB::select(
                'EXEC dbo.sp_update_user @user_id=?, @first_name=?, @last_name=?, @email=?, @contact=?, @role=?, @is_active=?',
                [
                    $user,
                    $validated['first_name'],
                    $validated['last_name'],
                    $validated['email'],
                    $validated['contact'],
                    $validated['role'],
                    (int) ($validated['is_active'] ?? 0),
                ]
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => 'Could not update user. Email/contact may already exist.',
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated successfully.');
    }
}
