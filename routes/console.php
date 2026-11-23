<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('safebuy:backup {--keep=14 : Number of daily backups to keep}', function () {
    $dir = storage_path('app/private/backups');
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $stamp = now()->format('Y-m-d_His');
    $driver = config('database.default');

    if ($driver === 'sqlite') {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath === ':memory:') {
            $this->info('In-memory SQLite database detected; skipping file copy.');

            return 0;
        }
        if (! is_file($dbPath)) {
            $this->error("SQLite database not found at {$dbPath}");

            return 1;
        }
        $target = "{$dir}/safebuy_{$stamp}.sqlite";
        copy($dbPath, $target);
        $this->info("SQLite backup saved to {$target}");
    } else {
        $this->warn("Automatic copy backup currently handles SQLite; configure mysqldump for {$driver} on production.");

        return 0;
    }

    // Prune old backups beyond --keep
    $keep = max(1, (int) $this->option('keep'));
    $files = glob("{$dir}/safebuy_*.sqlite") ?: [];
    rsort($files);
    foreach (array_slice($files, $keep) as $old) {
        @unlink($old);
    }

    return 0;
})->purpose('Create a daily database backup in storage/app/private/backups');

Schedule::command('safebuy:backup')->dailyAt('03:00');
