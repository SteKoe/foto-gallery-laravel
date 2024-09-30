<?php
namespace GalleryApp;

require_once 'cron.php';

use Illuminate\Support\Facades\Artisan;
use function GalleryApp\Commands\syncGallery;

Artisan::command('sync', function () {
    $this->comment('Syncing gallery...');
    syncGallery();
})->purpose('Display an inspiring quote');
