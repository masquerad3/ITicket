<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
  public function show(Request $request)
  {
    return view('pages.profile');
  }

  public function update(Request $request)
  {
    $user = auth()->user();

    // Ensure user is authenticated  
    if ($user === null) {
      abort(403);
    }

    // Validate only the fields you allow the user to edit
    $validated = $request->validate([
      'first_name' => ['required', 'string', 'max:50'],
      'last_name'  => ['required', 'string', 'max:50'],
      'email'      => ['required', 'email', 'max:50'],
      'contact'    => ['required', 'string', 'max:15'],
    ]);

    try {
      // IMPORTANT: role and is_active come from the authenticated user,
      // not from the request (prevents role escalation / deactivation attacks).
      $rows = DB::select(
        'EXEC dbo.sp_update_user @user_id = ?, @first_name = ?, @last_name = ?, @email = ?, @contact = ?, @role = ?, @is_active = ?',
        [
          $user->user_id,                 // or auth()->id() if primary key mapping is standard
          $validated['first_name'],
          $validated['last_name'],
          $validated['email'],
          $validated['contact'],
          $user->role,
          (int) $user->is_active,
        ]
      );

      // Optionally: if you want to ensure it updated something
      // $affected = $rows[0]->rows_affected ?? 0;

      return redirect()
        ->route('profile')
        ->with('status', 'Profile updated successfully.');
    } catch (\Throwable $e) {
      $msg = $e->getMessage();

      // Map SQL THROW messages to form field errors
      if (stripos($msg, 'Email already exists') !== false) {
        return back()->withErrors(['email' => 'Email already exists'])->withInput();
      }

      if (stripos($msg, 'Contact already exists') !== false) {
        return back()->withErrors(['contact' => 'Contact already exists'])->withInput();
      }

      return back()->withErrors(['error' => 'Could not update profile.'])->withInput();
    }
  }

  public function updatePassword(Request $request)
  {
    $user = auth()->user();

    if ($user === null) {
      abort(403);
    }

    $request->validate([
      'current_password' => ['required', 'string'],
      'password' => ['required', 'string', 'min:6', 'confirmed'],
    ]);

    // Verify the current password matches what is in the DB
    $currentPasswordOk = Hash::check($request->input('current_password'), $user->password_hash);

    if (!$currentPasswordOk) {
      throw ValidationException::withMessages([
        'current_password' => 'Current password is incorrect.',
      ]);
    }

    $newHash = Hash::make($request->input('password'));

    DB::select(
      'EXEC dbo.sp_update_user_password @user_id = ?, @password_hash = ?',
      [
        $user->user_id,
        $newHash,
      ]
    );

    // Optional: refresh model so auth()->user() has latest values
    $user->refresh();

    return redirect()
      ->route('profile')
      ->with('status', 'Password updated successfully.');
  }
}