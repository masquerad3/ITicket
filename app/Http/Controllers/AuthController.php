<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('pages.register');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:50'],
            'password' => ['required', 'string', 'min:1'],
        ]);

        // Call your stored procedure (NO inline SELECT)
        $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$data['email']]);

        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'email' => 'No account found for that email.',
            ]);
        }

        $row = $rows[0];

        // Optional: block inactive accounts
        if (isset($row->is_active) && (int)$row->is_active !== 1) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive.',
            ]);
        }

        if (!Hash::check($data['password'], $row->password_hash)) {
            throw ValidationException::withMessages([
                'password' => 'Incorrect password.',
            ]);
        }

        // Load a User model so Laravel Auth/session works nicely
        $user = User::findOrFail($row->user_id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:50'],
            'contact' => ['required', 'string', 'max:15'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $passwordHash = Hash::make($data['password']);

        // Call your stored procedure to create user
        // If the SP throws (duplicate email/contact), Laravel will return 500 unless we catch it.
        try {
            $result = DB::select(
                'EXEC dbo.sp_create_user @first_name=?, @last_name=?, @email=?, @contact=?, @password_hash=?, @role=?',
                [$data['first_name'], $data['last_name'], $data['email'], $data['contact'], $passwordHash, 'user']
            );
        } catch (\Throwable $e) {
            // Simple friendly message (you can improve by checking SQL error codes/messages)
            throw ValidationException::withMessages([
                'email' => 'Could not create account. Email/contact may already exist.',
            ]);
        }

        // Log in immediately after registration
        $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$data['email']]);
        $row = $rows[0] ?? null;

        if (!$row) {
            return redirect()->route('login.show')->with('status', 'Account created. Please log in.');
        }

        $user = User::findOrFail($row->user_id);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.show');
    }
}