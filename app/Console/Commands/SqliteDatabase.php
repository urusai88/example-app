<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SqliteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create_sqlite_database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fn = 'database.sqlite';
        $disk = Storage::disk('local');

        if (!$disk->exists($fn)) {
            $disk->put($fn, '');
        }

        return 0;
    }
}
