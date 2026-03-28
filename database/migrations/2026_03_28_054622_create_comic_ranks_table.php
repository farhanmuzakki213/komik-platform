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
    Schema::create('comic_ranks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
        $table->integer('rank'); // Peringkat pada hari tersebut
        $table->date('recorded_at'); // Tanggal rekam jejak
        $table->timestamps();

        // Mencegah duplikasi data komik yang sama pada hari yang sama
        $table->unique(['comic_id', 'recorded_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comic_ranks');
    }
};
