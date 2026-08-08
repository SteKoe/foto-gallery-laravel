<?php

namespace App\Console\Commands;

use App\Services\GallerySyncService;
use Illuminate\Console\Command;

class ListGalleriesCommand extends Command
{
    protected $signature = 'gallery:list';

    protected $description = 'List remote galleries';

    public function handle(GallerySyncService $syncService): int
    {
        $this->info('Fetching remote galleries...');
        $this->newLine();

        try {
            $syncService->setOptions($this->options());
            $remote = $syncService->listRemoteFiles();

            if (empty($remote)) {
                $this->comment('No galleries found on remote storage.');

                return self::SUCCESS;
            }

            $this->info('Available Remote Galleries:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            foreach (array_values($remote) as $index => $name) {
                $this->line(($index + 1).'. <comment>'.$name.'</comment>');
            }
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();
            $this->info('Total galleries: <comment>'.count($remote).'</comment>');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred while listing galleries:');
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
