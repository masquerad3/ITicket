<?php // Start of a PHP file.

//These are imports (like “include these Laravel classes so we can use them”):
use Illuminate\Database\Migrations\Migration; // Migration: the base class migrations extend.
use Illuminate\Database\Schema\Blueprint; // Blueprint: the object used to define columns (like $table->string(...)).
use Illuminate\Support\Facades\Schema; // Schema: Laravel’s “schema builder” used to create/drop tables.

// This file is a Laravel migration: a PHP script that tells Laravel how to create (up) and undo (down) 
// a database change. In this case: create a USERS table with specific columns.

// This creates an anonymous class (a class with no name) that extends Migration. 
// Laravel supports this style by default now. It’s the same as defining a named class, just shorter.
return new class extends Migration
{
    /**
     * When you run ("php artisan migrate") Laravel runs the up() method.
     * Run the migrations.
     * 
     * public means Laravel can call it.
     * up is the “do it” method.
     * : void means it returns nothing.
     */
    public function up(): void
    {   // Create a table named USERS.
        Schema::create('USERS', function (Blueprint $table) {
            $table->id('user_id'); // Creates an auto-incrementing primary key column named user_id.
            $table->string('first_name', 50); // Creates text columns: up to # characters
            $table->string('last_name', 50);
            $table->string('email', 50)->unique(); // unique(): no two rows can have the same value in this column.
            $table->string('contact', 15)->unique();
            $table->string('password_hash', 255); // Store hashed passwords, not plain text. (Laravel usually stores bcrypt hashes here).
            $table->string('role', 20)->default('user'); // Default role is 'user'. 
            $table->boolean('is_active')->default(true); // Creates a true/false flag (SQL Server uses BIT). default true. 
            $table->timestamps(); // Creates two columns automatically: created_at, updated_at
        });
    }

    /**
     * If you want to undo ("php artisan migrate:rollback") Laravel runs the down() method.
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('USERS'); // Drops (deletes) the USERS table if it exists.
    }
};
