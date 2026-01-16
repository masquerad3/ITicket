<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TICKET_MESSAGES', function (Blueprint $table) {
            $table->id('message_id');

            $table->foreignId('ticket_id')
                ->constrained('TICKETS', 'ticket_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreignId('user_id')
                ->constrained('USERS', 'user_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->string('message_type', 20)->default('public'); // public | internal
            $table->text('body');

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TICKET_MESSAGES');
    }
};
