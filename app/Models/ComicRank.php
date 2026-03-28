<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComicRank extends Model
{
    /**
     * FAKTA: Menggunakan $guarded = [] mengizinkan kita melakukan mass-assignment
     * (seperti ComicRank::create([...])) tanpa harus mendefinisikan $fillable satu per satu.
     * Ini aman selama kita mengontrol input dari backend (bukan langsung dari request user).
     */
    protected $guarded = [];

    /**
     * FAKTA: Melakukan "Casting" sangat penting untuk data analitik.
     * Ini memaksa Laravel untuk selalu menganggap kolom 'recorded_at'
     * sebagai objek tanggal (Carbon), bukan sekadar string (teks).
     */
    protected $casts = [
        'recorded_at' => 'date',
    ];

    /**
     * Relasi Inverse (BelongsTo)
     * Menghubungkan riwayat peringkat ini kembali ke komik aslinya.
     */
    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
}
