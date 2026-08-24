<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\WorkEntry;

#[Signature('app:backfill-work-entry-sort-order')]
#[Description('Backfill zero work entry sort order values without changing manually sorted rows.')]
class BackfillWorkEntrySortOrder extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = 0;

        WorkEntry::where('sort_order', 0)
            ->orderBy('id')
            ->get(['id'])
            ->each(function (WorkEntry $entry, int $index) use (&$updated): void {
                $entry->update(['sort_order' => ($index + 1) * 10]);
                $updated++;
            });

        $this->info("Updated {$updated} work entries.");

        return self::SUCCESS;
    }
}
