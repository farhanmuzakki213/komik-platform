<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'chapter_number',
        'title',
        'thumbnail',
        'creator_note',
        'allow_comments',
        'status',
        'admin_notes',
        'published_at',
    ];

    protected $casts = [
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }

    public function panels()
    {
        return $this->hasMany(Panel::class)->orderBy('order_index', 'asc');
    }

    // Relasi Polymorphic
    public function likes() {
        return $this->morphMany(Like::class, 'likeable');
    }

    // Mengecek apakah user saat ini sudah menyukai konten ini
    public function isLikedBy($user) {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
