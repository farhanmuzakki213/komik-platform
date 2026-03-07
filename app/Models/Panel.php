<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panel extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'image_path',
        'order_index',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
