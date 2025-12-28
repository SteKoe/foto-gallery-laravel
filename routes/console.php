<?php
namespace GalleryApp;

use Illuminate\Support\Facades\Artisan;
use App\Services\GallerySyncService;

Artisan::command('list', function (GallerySyncService $syncService) {
    $this->info('Fetching remote galleries...');
    $this->newLine();

    try {
        $syncService->setOptions($this->options());
        $remote = $syncService->listRemoteFiles();

        if (empty($remote)) {
            $this->comment('No galleries found on remote storage.');
            return 0;
        }

        $this->info('Available Remote Galleries:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        foreach (array_values($remote) as $index => $name) {
            $this->line(($index + 1) . '. <comment>' . $name . '</comment>');
        }
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->info('Total galleries: <comment>' . count($remote) . '</comment>');

        return 0;
    } catch (\Exception $e) {
        $this->error('An error occurred while listing galleries:');
        $this->error($e->getMessage());
        if ($this->output->isVerbose()) {
            $this->error($e->getTraceAsString());
        }
        return 1;
    }
})->purpose('List remote galleries');

Artisan::command('sync {--name=} {--skip-download} {--force}', function (GallerySyncService $syncService) {
    $name = $this->option('name');
    $skipDownload = $this->option('skip-download');
    $force = $this->option('force');

    $this->info('Starting gallery sync...');
    if ($name) {
        $this->line("Gallery: <info>{$name}</info>");
    } else {
        $this->line('Gallery: <info>All</info>');
    }

    // Display options
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
            return 1;
        }

        // Display results
        $this->newLine();
        $this->info('Sync Results:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $filesToDownloadCount = count($gallerySyncResult->filesToDownload);
        $filesToRemoveCount = count($gallerySyncResult->filesToRemove);
        $filesUntouchedCount = count($gallerySyncResult->filesUntouched);
        $totalFilesCount = $filesToDownloadCount + $filesToRemoveCount + $filesUntouchedCount;

        $this->line("Total files checked: <comment>{$totalFilesCount}</comment>");
        $this->newLine();

        if ($filesToDownloadCount > 0) {
            $this->line("Files to download: <info>{$filesToDownloadCount}</info>");
            if ($this->output->isVerbose()) {
                foreach ($gallerySyncResult->filesToDownload as $file) {
                    $this->line("  • {$file->displayname} ({$file->fileid})");
                }
            }
        } else {
            $this->line('Files to download: <comment>0</comment>');
        }

        if ($filesToRemoveCount > 0) {
            $this->line("Files to remove: <error>{$filesToRemoveCount}</error>");
            if ($this->output->isVerbose()) {
                foreach ($gallerySyncResult->filesToRemove as $file) {
                    $this->line("  • {$file->displayname} ({$file->fileid})");
                }
            }
        } else {
            $this->line('Files to remove: <comment>0</comment>');
        }

        if ($filesUntouchedCount > 0) {
            $this->line("Files untouched: <comment>{$filesUntouchedCount}</comment>");
            if ($this->output->isVerbose()) {
                foreach ($gallerySyncResult->filesUntouched as $file) {
                    $this->line("  • {$file->displayname} ({$file->fileid})");
                }
            }
        } else {
            $this->line('Files untouched: <comment>0</comment>');
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($filesToDownloadCount === 0 && $filesToRemoveCount === 0) {
            $this->comment('Gallery is already up to date!');
            return 0;
        }

        $this->info('Sync completed successfully!');
        return 0;
    } catch (\Exception $e) {
        $this->error('An error occurred during sync:');
        $this->error($e->getMessage());
        if ($this->output->isVerbose()) {
            $this->error($e->getTraceAsString());
        }
        return 1;
    }
})->purpose('Sync gallery images from remote storage');

Artisan::command('clean {--name=}', function (GallerySyncService $syncService) {
    $name = $this->option('name');

    $this->info('Starting gallery clean...');
    if ($name) {
        $this->line("Gallery: <info>{$name}</info>");
    } else {
        $this->line('Gallery: <info>All</info>');
    }

    try {
        $this->newLine();
        $this->info('Analyzing local files...');
        $syncService->cleanGallery($name);

        $this->newLine();
        $this->info('Clean Results:');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $galleryDisplay = $name ?: 'All';
        $this->line("Cleaning completed for gallery: <comment>{$galleryDisplay}</comment>");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->info('Clean completed successfully!');
        return 0;
    } catch (\Exception $e) {
        $this->error('An error occurred during clean:');
        $this->error($e->getMessage());
        if ($this->output->isVerbose()) {
            $this->error($e->getTraceAsString());
        }
        return 1;
    }
})->purpose('Clean gallery images from local storage');
