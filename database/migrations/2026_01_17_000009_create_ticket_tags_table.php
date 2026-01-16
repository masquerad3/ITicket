<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TICKET_TAGS', function (Blueprint $table) {
            $table->id('tag_id');

            $table->foreignId('ticket_id')
                ->constrained('TICKETS', 'ticket_id')
                ->onUpdate('no action')
                ->onDelete('no action');

            $table->string('tag', 50);
            $table->timestamps();

            $table->unique(['ticket_id', 'tag']);
            $table->index(['ticket_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TICKET_TAGS');
    }
};
