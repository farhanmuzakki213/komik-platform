<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Aksi',
            'Romantis',
            'Komedi',
            'Fantasi',
            'Horor',
            'Misteri',
            'Slice of Life',
            'Drama',
            'Sci-Fi',
            'Kerajaan',
            'Isekai',
            'Olahraga'
        ];

        foreach ($genres as $genre) {
            Genre::firstOrCreate([
                'slug' => Str::slug($genre)
            ], [
                'name' => $genre
            ]);
        }
    }
}
