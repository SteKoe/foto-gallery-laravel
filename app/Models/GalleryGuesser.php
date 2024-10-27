<?php

namespace App\Models;

use App\Utils\FileUtils;

abstract class GalleryGuesser
{
    protected string $GALLERY_IMAGE_REGEX = '/[^\/]*\.(jpe?g)$/i';

    abstract public function accept($href);

    /**
     * @param $href
     * @return array
     */
    abstract public function getGallery($href): array;

    /**
     * @param $href
     * @return string
     */
    abstract public function getSection($href): ?string;

    protected function createSlug($name): string
    {
        return FileUtils::createSlug($name);
    }
}
