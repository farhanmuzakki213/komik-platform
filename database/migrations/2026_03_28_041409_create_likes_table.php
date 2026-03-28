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
    // Tabel sentral untuk semua Likes
    Schema::create('likes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->morphs('likeable');
        $table->timestamps();
        $table->unique(['user_id', 'likeable_id', 'likeable_type']);
    });

    Schema::table('comics', function (Blueprint $table) {
        $table->unsignedBigInteger('views_count')->default(0);
        $table->unsignedBigInteger('likes_count')->default(0);
    });

    Schema::table('chapters', function (Blueprint $table) {
        $table->unsignedBigInteger('views_count')->default(0);
        $table->unsignedBigInteger('likes_count')->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
