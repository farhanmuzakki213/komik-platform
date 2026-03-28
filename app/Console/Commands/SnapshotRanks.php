<?php

namespace App\Console\Commands;

use App\Models\Comic;
use App\Models\ComicRank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranks:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyimpan snapshot peringkat top 30 komik harian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('comic_ranks')->truncate();

        // 2. Ambil 30 komik teratas saat ini
        $comics = Comic::orderByDesc('views_count')->take(30)->get();

        // 3. ACAK urutannya agar seolah-olah kemarin urutannya berbeda
        $shuffledComics = $comics->shuffle();

        foreach ($shuffledComics as $index => $comic) {
            ComicRank::create([
                'comic_id' => $comic->id,
                'rank' => $index + 1, // Peringkat acak dari 1-30
                'recorded_at' => now()->subDay()->toDateString() // Simpan dengan tanggal KEMARIN
            ]);
        }

        $this->info('Snapshot simulasi peringkat KEMARIN berhasil dibuat!');
    }
}
