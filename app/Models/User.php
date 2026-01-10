<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // SQL Server table name
    protected $table = 'USERS';

    // Primary key column name
    protected $primaryKey = 'user_id';

    // If your key is auto-incrementing (it is)
    public $incrementing = true;

    // user_id is an int
    protected $keyType = 'int';

    // Allow mass assignment for these (optional, used for Model::create)
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'contact',
        'password_hash',
        'role',
        'is_active',
    ];

    // Hide password hash in arrays/json
    protected $hidden = [
        'password_hash',
    ];

    /**
     * IMPORTANT:
     * Laravel Auth expects the password column to be named "password".
     * This tells Laravel to use "password_hash" instead when checking credentials.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}