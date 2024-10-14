<?php

namespace App\Models;

class GuessGalleryWithoutDateInHref extends GalleryGuesser
{
    private string $GALLERY_NAME_REGEX = '/\d{4}\.\d{2}(\.\d{2})?[\-\s][^\/]+/';
    private string $SECTION_PATTERN = '/\d{4}\.\d{2}/';

    public function accept($href): bool
    {
        $decodedHref = urldecode($href);
        return preg_match($this->GALLERY_NAME_REGEX, $decodedHref) === 0;
    }

    public function getGallery($href): array
    {
        $decodedHref = urldecode($href);
        $galleryName = $this->getGalleryName($decodedHref);
        $gallerySlug = $this->createSlug($galleryName);

        return [
            'name' => $galleryName,
            'slug' => $gallerySlug,
        ];
    }

    private function getGalleryName($href) {
        $split = explode('/', $href);
        $splitReversed = array_reverse($split);

        foreach ($splitReversed as $a) {
            if (!preg_match($this->GALLERY_IMAGE_REGEX, $a) && !preg_match($this->SECTION_PATTERN, $a)) {
                return $a;
            }
        }
        return null;
    }

    public function getSection($href): string {
        $split = explode('/', $href);
        $splitReversed = array_reverse($split);

        foreach ($splitReversed as $a) {
            if (preg_match($this->SECTION_PATTERN, $a)) {
                return $a;
            }
        }
        return '';
    }
}
