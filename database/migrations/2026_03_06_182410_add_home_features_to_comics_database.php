<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comics', function (Blueprint $table) {
            $table->enum('release_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable()->after('status');
            $table->unsignedBigInteger('total_views')->default(0)->after('release_day');
            $table->unsignedBigInteger('total_favorites')->default(0)->after('total_views');
            $table->decimal('rating', 3, 2)->default(0.00)->after('total_favorites');
            $table->index(['release_day', 'status']);
            $table->index(['total_views', 'status']);
        });

        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('comic_genre', function (Blueprint $table) {
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->primary(['comic_id', 'genre_id']);
        });

        Schema::create('comic_daily_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->date('view_date');
            $table->unsignedInteger('views_count')->default(1);

            $table->unique(['comic_id', 'view_date']);
            $table->index(['view_date']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('comic_id')->constrained('comics')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'comic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('comic_daily_views');
        Schema::dropIfExists('comic_genre');
        Schema::dropIfExists('genres');

        Schema::table('comics', function (Blueprint $table) {
            $table->dropColumn(['release_day', 'total_views', 'total_favorites', 'rating']);
        });
    }
};
