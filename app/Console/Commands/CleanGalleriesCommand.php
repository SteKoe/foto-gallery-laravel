<?php

namespace App\Console\Commands;

use App\Services\GallerySyncService;
use Illuminate\Console\Command;

class CleanGalleriesCommand extends Command
{
    protected $signature = 'gallery:clean
        {--name= : The name of the gallery to clean}
        {--force : Skip confirmation prompt}';

    protected $description = 'Clean gallery images from local storage';

    public function handle(GallerySyncService $syncService): int
    {
        $name = $this->option('name');

        $this->info('Starting gallery clean...');
        $this->line($name
            ? "Gallery: <info>{$name}</info>"
            : 'Gallery: <info>All</info>'
        );

        try {
            $this->newLine();
            $this->info('Analyzing local files...');

            if (! $this->option('force')) {
                $galleryDisplay = $name ?: 'All';
                if (! $this->confirm("Do you really want to delete gallery \"{$galleryDisplay}\" from local storage?")) {
                    $this->comment('Aborted.');

                    return self::SUCCESS;
                }
            }

            $syncService->cleanGallery($name);

            $this->newLine();
            $this->info('Clean Results:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $galleryDisplay = $name ?: 'All';
            $this->line("Cleaning completed for gallery: <comment>{$galleryDisplay}</comment>");
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $this->info('Clean completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during clean:');
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
