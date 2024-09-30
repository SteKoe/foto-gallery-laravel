<?php
namespace GalleryApp\Commands;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

require_once './vendor/autoload.php';

function syncGallery()
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./public/images/gallery'));

    $imageManager = new ImageManager(new Driver());
    foreach ($iterator as $file) {
        if ($file->isFile() && !str_contains($file->getFilename(), 'thumb_')) {
            $fullPath = $file->getPathname();
            $dst = $imageManager->read($fullPath);
            $dst->scale(1024, 1024);
            $dirname = pathinfo($fullPath)['dirname'];
            $basename = pathinfo($fullPath)['basename'];
            $output = $dirname . '/thumb_' . $basename;
            $dst->toJpeg(90)->save($output);

            echo "Generated thumbnail for $fullPath\n";
        }
    }
}
