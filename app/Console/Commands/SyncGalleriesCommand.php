<?php

namespace App\Console\Commands;

use App\Services\GallerySyncService;
use Illuminate\Console\Command;

class SyncGalleriesCommand extends Command
{
    protected $signature = 'gallery:sync
        {--name= : The name of the gallery to sync}
        {--skip-download : Skip downloading image files}
        {--force : Force sync all remote files}';

    protected $description = 'Sync gallery images from remote storage';

    public function handle(GallerySyncService $syncService): int
    {
        $name = $this->option('name');
        $skipDownload = $this->option('skip-download');
        $force = $this->option('force');

        $this->info('Starting gallery sync...');
        $this->line($name
            ? "Gallery: <info>{$name}</info>"
            : 'Gallery: <info>All</info>'
        );

        $options = [];
        if ($skipDownload) {
            $options['skip-download'] = true;
            $this->line('Option: <comment>Skip download enabled</comment>');
        }
        if ($force) {
            $options['force'] = true;
            $this->line('Option: <comment>Force sync enabled</comment>');
        }

        $syncService->setOptions($options);

        try {
            $this->newLine();
            $this->info('Analyzing files...');
            $gallerySyncResult = $syncService->syncGallery($name);

            if ($gallerySyncResult === null) {
                $this->error('Error syncing galleries. Please check the logs for more details.');

                return self::FAILURE;
            }

            $this->displaySyncResults($gallerySyncResult);

            if (count($gallerySyncResult->filesToDownload) === 0 && count($gallerySyncResult->filesToRemove) === 0) {
                $this->comment('Gallery is already up to date!');

                return self::SUCCESS;
            }

            $this->info('Sync completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('An error occurred during sync:');
            $this->error($e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function displaySyncResults($gallerySyncResult): void
    {
        $this->newLine();
        $this->info('Sync Results:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $filesToDownloadCount = count($gallerySyncResult->filesToDownload);
        $filesToRemoveCount = count($gallerySyncResult->filesToRemove);
        $filesUntouchedCount = count($gallerySyncResult->filesUntouched);
        $totalFilesCount = $filesToDownloadCount + $filesToRemoveCount + $filesUntouchedCount;

        $this->line("Total files checked: <comment>{$totalFilesCount}</comment>");
        $this->newLine();

        $this->displayFileList('Files to download', $gallerySyncResult->filesToDownload, $filesToDownloadCount, 'info');
        $this->displayFileList('Files to remove', $gallerySyncResult->filesToRemove, $filesToRemoveCount, 'error');
        $this->displayFileList('Files untouched', $gallerySyncResult->filesUntouched, $filesUntouchedCount, 'comment');

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
    }

    private function displayFileList(string $label, array $files, int $count, string $style): void
    {
        if ($count > 0) {
            $this->line("{$label}: <{$style}>{$count}</{$style}>");
            if ($this->output->isVerbose()) {
                foreach ($files as $file) {
                    $this->line("  • {$file->displayname} ({$file->fileid})");
                }
            }
        } else {
            $this->line("{$label}: <comment>0</comment>");
        }
    }
}
