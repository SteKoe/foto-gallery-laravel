<?php

namespace App\Models;

class GuessGalleryByDateInHref extends GalleryGuesser {
    private string $GALLERY_NAME_REGEX = '/\d{4}\.\d{2}(\.\d{2})?[^\/]+/i';
    private string $GALLERY_SECTION_REGEX = '/\d{4}\.\d{2}(\.\d{2})?[^\/]+\/(?<section>[^\/]*)\/[^\/]*\.(jpe?g)$/i';

    public function accept($href): bool
    {
        $decodedHref = urldecode($href);
        return preg_match($this->GALLERY_NAME_REGEX, $decodedHref) === 1;
    }

    public function getGallery($href): array
    {
        $decodedHref = urldecode($href);

        // Use preg_match to extract the gallery name
        if (preg_match($this->GALLERY_NAME_REGEX, $decodedHref, $matches)) {
            $galleryName = $matches[0];
        } else {
            $galleryName = '';
        }

        // Assuming createSlug() is a defined method
        $gallerySlug = $this->createSlug($galleryName);

        // Return a new Gallery instance (assuming the Gallery class accepts an array)
        return [
            'name' => $galleryName,
            'slug' => $gallerySlug,
        ];
    }

    public function getSection($href): string
    {
        // Match the section from the URL
        if (preg_match($this->GALLERY_SECTION_REGEX, $href, $matches)) {
            $section = $matches['section'] ?? '';
        } else {
            $section = '';
        }

        return urldecode($section);
    }
}
