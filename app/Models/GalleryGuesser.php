<?php

namespace App\Models;

abstract class GalleryGuesser
{
    protected string $GALLERY_IMAGE_REGEX = '/[^\/]*\.(jpe?g)$/i';

    abstract public function accept($href);

    abstract public function getGallery($href);

    abstract public function getSection($href);

    protected function createSlug($name): string
    {
        $string = strtolower($name);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-z0-9\s\-.]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
}
