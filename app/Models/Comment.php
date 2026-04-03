<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function replies() {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    /**
     * Relasi Polymorphic untuk Interaksi Like/Dislike
     */
    public function reactions()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
