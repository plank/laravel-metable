<?php

namespace Plank\Metable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Plank\Metable\Meta;

class RefreshMeta extends Command
{
    protected $signature = 'metable:refresh';

    protected $description = 'Re-encode all stored meta values to update types and indexes.';

    public function handle(): void
    {
        $this->info('Refreshing meta values...');

        $count = 0;
        $total = DB::table('meta')->count();
        $lastId = null;

        $progress = $this->output->createProgressBar($total);
        $progress->start();

        while ($count < $total) {
            $query = Meta::query()
                ->orderBy('id')
                ->limit(config('metable.refreshPageSize', 100));
            if ($lastId) {
                $query->where('id', '>', $lastId);
            }

            $collection = $query->get();
            /** @var Meta $meta */
            foreach ($collection as $meta) {
                $value = $meta->value;
                $meta->setValueAttribute(null);
                $meta->setValueAttribute($value);
                $meta->save();
                $count++;
                $progress->advance();
                $lastId = $meta->id;
            }
        }

        $progress->finish();

        $this->info('Refresh complete.');
    }
}
