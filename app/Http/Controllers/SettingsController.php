<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class SettingsController extends Controller
{
    public function index()
    {
        $migrator = app('migrator');
        $repository = $migrator->getRepository();

        $files = $migrator->getMigrationFiles([database_path('migrations')]);
        $ran = [];
        $dbError = null;

        try {
            $ran = $repository->repositoryExists() ? $repository->getRan() : [];
            $batches = $repository->repositoryExists() ? $repository->getMigrationBatches() : [];
        } catch (Throwable $e) {
            $dbError = $e->getMessage();
            $batches = [];
        }

        $migrations = collect($files)->map(fn ($path, $name) => [
            'name' => $name,
            'ran' => in_array($name, $ran, true),
            'batch' => $batches[$name] ?? null,
        ])->values();

        return view('settings.index', [
            'migrations' => $migrations,
            'pending' => $migrations->where('ran', false)->count(),
            'dbError' => $dbError,
            'env' => [
                'Aplikacija' => config('app.env') . ($this->debug() ? ' (debug)' : ''),
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Baza' => config('database.default') . ' / ' . (DB::connection()->getDatabaseName() ?? '—'),
            ],
        ]);
    }

    public function migrate()
    {
        return $this->run('migrate', ['--force' => true], 'Migracije su pokrenute.');
    }

    public function clearCache()
    {
        return $this->run('optimize:clear', [], 'Keš je očišćen.');
    }

    private function run(string $command, array $options, string $message)
    {
        try {
            Artisan::call($command, $options);
            $output = trim(Artisan::output());
            $status = $message;
        } catch (Throwable $e) {
            $output = $e->getMessage();
            $status = null;
        }

        $redirect = redirect()->route('platform.settings.index')->with('output', $output ?: 'Nema izlaza.');

        return $status
            ? $redirect->with('status', $status)
            : $redirect->withErrors(['migrate' => 'Komanda nije uspela, pogledaj izlaz ispod.']);
    }

    private function debug(): bool
    {
        return (bool) config('app.debug');
    }
}
