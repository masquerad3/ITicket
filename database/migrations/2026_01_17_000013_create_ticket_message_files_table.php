<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TICKET_MESSAGE_FILES', function (Blueprint $table) {
            $table->id('file_id');

            $table->foreignId('message_id')
                ->constrained('TICKET_MESSAGES', 'message_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->foreignId('uploaded_by')
                ->constrained('USERS', 'user_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->string('stored_path', 255);
            $table->string('original_name', 255);
            $table->string('mime', 80)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();

            $table->index(['message_id', 'created_at']);
            $table->unique(['message_id', 'stored_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TICKET_MESSAGE_FILES');
    }
};
