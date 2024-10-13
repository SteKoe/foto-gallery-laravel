<?php
namespace GalleryApp;

use App\Services\GalleryService;
use Illuminate\Support\Facades\Artisan;
use App\Services\GallerySyncService;

Artisan::command('sync {--slug=}', function (GallerySyncService $webdavService) {
    $slug = $this->option('slug');
    $webdavService->syncGallery($slug);
})->purpose('Display an inspiring quote');

Artisan::command('clean {--slug=}', function (GallerySyncService $webdavService) {
    $slug = $this->option('slug');
    $webdavService->cleanGallery($slug);
})->purpose('Display an inspiring quote');
