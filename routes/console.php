<?php
namespace GalleryApp;

use App\Services\GalleryService;
use Illuminate\Support\Facades\Artisan;
use App\Services\GallerySyncService;

Artisan::command('sync {--name=} {--skip-download}', function (GallerySyncService $syncService) {
    $name = $this->option('name');

    $syncService->setOptions([
        'skip-download' => $this->option('skip-download'),
    ]);
    $syncService->syncGallery($name);
})->purpose('Display an inspiring quote');

Artisan::command('clean {--name=}', function (GallerySyncService $webdavService) {
    $name = $this->option('name');
    $webdavService->cleanGallery($name);
})->purpose('Display an inspiring quote');
