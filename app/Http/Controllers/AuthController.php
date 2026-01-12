<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; // Request object contains everything sent by the browser (form fields, cookies, etc.)
use Illuminate\Support\Facades\Auth; // Auth facade = login/logout helpers (session-based authentication)
use Illuminate\Support\Facades\DB; // DB facade = run database queries / stored procedures
use Illuminate\Support\Facades\Hash; // Hash facade = hash passwords securely + check password matches
use Illuminate\Validation\ValidationException; // Used to manually return "validation-style" errors (so they show in $errors in Blade)

class AuthController extends Controller
{
    /**
     * Handles POST /login
     * Goal: validate inputs -> find user by email -> verify password -> log in -> redirect
     */
    public function login(Request $request)
    {
        // Validate the incoming form data.
        // If validation fails, Laravel automatically redirects back and fills $errors in the view.
        // If it passes, $data will only contain the validated fields.
        $data = $request->validate([
            // required = must be present | string = must be text | email = must look like an email
            // max:50   = limit length to protect DB and data consistency
            'email' => ['required', 'string', 'email', 'max:50'],
            // min:1    = basically means "not empty"
            'password' => ['required', 'string', 'min:1'],
        ]);

        // Call SQL Server stored procedure to find the user by email.
        // DB::select returns an array of rows (each row is an object with properties = column names). 
        // Example: [ { user_id: 5, email: 'a@b.c', password_hash: '...' }, ... ]
        // Using ? with an array is parameter binding (prevents SQL injection).
        $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$data['email']]);

        // If no rows returned (email doesn't exist in the DB):
        if (count($rows) === 0) {
            // Throw a validation error attached to the "email" field.
            // This shows up in Blade using $errors.
            throw ValidationException::withMessages([
                'email' => 'No account found for that email.',
            ]);
        }

        // Get the first (and only) row, since emails are unique.
        $row = $rows[0];

        // if your SP returns is_active, you can block users who are inactive (disabled accounts).
        if (isset($row->is_active) && (int)$row->is_active !== 1) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive.',
            ]);
        }

        // Check if the plain password from the form matches the stored hash in the DB.
        if (!Hash::check($data['password'], $row->password_hash)) {
            throw ValidationException::withMessages([
                'password' => 'Incorrect password.',
            ]);
        }

        // Convert the raw DB row into an actual User model.
        // findOrFail will throw an error if the ID doesn't exist (should not happen if DB is consistent).
        $user = User::findOrFail($row->user_id);

        // Log the user in (stores user ID in the session).
        Auth::login($user);

        // Security: regenerate session ID after login to prevent session fixation attacks.
        $request->session()->regenerate();

        // Send the browser to the dashboard page after successful login.
        return redirect()->route('dashboard');
    }

    /**
     * Handles POST /register
     * Goal: validate inputs -> hash password -> create user in DB via SP -> log in -> redirect
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:50'],
            'contact' => ['required', 'string', 'max:15'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Hash the password BEFORE storing it.
        // This creates a bcrypt hash (safe to store in DB).
        $passwordHash = Hash::make($data['password']);

        // Call your stored procedure to create user
        // We wrap in try/catch because SQL Server may throw errors (duplicate email/contact, etc.)
        try {
            $result = DB::select(
                // Stored procedure call with parameters | 'user' role by default
                'EXEC dbo.sp_create_user @first_name=?, @last_name=?, @email=?, @contact=?, @password_hash=?, @role=?',
                [$data['first_name'], $data['last_name'], $data['email'], $data['contact'], $passwordHash, 'user']
            );
        } catch (\Throwable $e) {
            // If SQL fails, return a "validation-style" error instead of a 500 crash.
            // (You can improve this later by checking exact SQL error message / code.)
            throw ValidationException::withMessages([
                'email' => 'Could not create account. Email/contact may already exist.',
            ]);
        }

        // After creating the user, fetch them back from DB so we can get user_id.
        $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$data['email']]);
        $row = $rows[0] ?? null; // If no row exists, set to null
        
        // Safety fallback: if for some reason we can't read the user back, redirect to login.
        if (!$row) {
            return redirect()->route('login')->with('status', 'Account created. Please log in.');
        }

        // Load the new user as a User model so Laravel Auth can log them in.
        $user = User::findOrFail($row->user_id);

        // Log them in immediately after registration.
        Auth::login($user);

        // Regenerate session for security (same reason as login()).
        $request->session()->regenerate();

        // Take them to dashboard after successful registration.
        return redirect()->route('dashboard');
    }

    /**
     * Handles POST /logout
     * Goal: remove login session -> clear session -> redirect to login
     */
    public function logout(Request $request)
    {
        // Log the user out (removes auth user from session)
        Auth::logout();

        // Invalidate the whole session (clears session data)
        $request->session()->invalidate();

        // Generate a new CSRF token (prevents token reuse)
        $request->session()->regenerateToken();

        // Redirect back to login page
        return redirect()->route('login');
    }
}