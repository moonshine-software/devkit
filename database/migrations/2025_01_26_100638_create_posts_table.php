<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see Post
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name');
            $table->string('slug');
            $table->text('text');
            $table->json('data')->nullable();
            $table->json('enums')->nullable();

            $table->timestamps();
        });

        Schema::create('category_post', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Category::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignIdFor(Post::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('pivot_field')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_post');
        Schema::dropIfExists('posts');
    }
};
