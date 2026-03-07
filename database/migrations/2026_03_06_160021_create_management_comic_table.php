<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prequel_id')->nullable()->constrained('comics')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('synopsis');
            $table->string('square_thumbnail')->nullable();
            $table->string('vertical_thumbnail');
            $table->string('banner_image')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected'])->default('draft');
            $table->boolean('is_adult')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->float('chapter_number');
            $table->string('title');
            $table->text('creator_note')->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected'])->default('draft');
            $table->text('admin_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['comic_id', 'chapter_number']);
        });

        Schema::create('panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->string('image_path');
            $table->integer('order_index');
            $table->timestamps();
            $table->index(['chapter_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panels');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('comics');
    }
};
