<?php

namespace App\Models;

// This is Laravel's base User class for authentication.
// It already includes features needed for login/session (it implements "Authenticatable").
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// Allows this user to receive notifications (email notifications, database notifications, etc.)
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable; // Adds notification-related methods to the User model

    // Tell Laravel which DB table this model uses.
    // Laravel default would be "users" (lowercase), but your SQL Server table is "USERS".
    protected $table = 'USERS';

    // Tell Laravel the primary key column name.
    // Laravel default is "id", but your table uses "user_id".
    protected $primaryKey = 'user_id';

    // Tells Laravel the primary key uses auto-increment (1,2,3,...).
    // Most SQL Server identity columns are incrementing.
    public $incrementing = true;

    // Primary key data type.
    // Laravel default is string for UUID setups sometimes, but yours is an integer.
    protected $keyType = 'int';

    // If true: Laravel expects created_at and updated_at columns and will automatically write them.
    // If your SQL Server table has these columns, keep this true.
    // If your table does NOT have them, set this to false to avoid SQL errors.
    public $timestamps = true;

    // "Mass assignment" fields:
    // If you ever do User::create([...]) or $user->fill([...]),
    // Laravel will ONLY allow these fields to be set in bulk.
    // This helps prevent security issues (someone setting fields you didn't expect).
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'contact',
        'password_hash',
        'role',
        'is_active',
        'profile_photo_path',
    ];

    // Fields hidden when the model is converted to an array/JSON.
    // Example: return response()->json(auth()->user());
    // password_hash will not be included in the JSON output.
    protected $hidden = [
        'password_hash',
    ];

    /**
     * IMPORTANT FOR AUTH:
     *
     * Laravel Auth expects the password column to be named "password".
     * But your database uses "password_hash".
     *
     * This method tells Laravel:
     * "When you need the user's password for authentication, use password_hash."
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}