<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TICKETS', function (Blueprint $table) {
            $table->id('ticket_id');

            $table->foreignId('user_id')
                ->constrained('USERS', 'user_id')
                ->cascadeOnDelete();

            $table->string('subject', 255);
            $table->string('category', 50);
            $table->string('priority', 10)->default('Medium');
            $table->string('department', 100)->nullable();
            $table->string('location', 100)->nullable();
            $table->text('description');
            $table->string('preferred_contact', 10)->default('email');

            $table->string('status', 20)->default('open');
            $table->json('attachments')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TICKETS');
    }
};
