<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'prequel_id',
        'title',
        'slug',
        'synopsis',
        'square_thumbnail',
        'vertical_thumbnail',
        'banner_image',
        'status',
        'is_adult',
        'release_day',
        'total_views',
        'total_favorites',
        'rating',
        'published_at',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'comic_genre');
    }

    public function prequel()
    {
        return $this->belongsTo(Comic::class, 'prequel_id');
    }

    public function sequels()
    {
        return $this->hasMany(Comic::class, 'prequel_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
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
