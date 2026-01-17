<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('KB_CATEGORIES', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('KB_ARTICLES', function (Blueprint $table) {
            $table->id('article_id');

            $table->foreignId('category_id')
                ->constrained('KB_CATEGORIES', 'category_id')
                ->cascadeOnDelete();

            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->string('summary', 500)->nullable();
            $table->longText('content_html');

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('view_count')->default(0);

            $table->foreignId('created_by')
                ->constrained('USERS', 'user_id');

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('USERS', 'user_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KB_ARTICLES');
        Schema::dropIfExists('KB_CATEGORIES');
    }
};
