<?php

namespace App\Services;

use App\Models\GalleryImage;
use App\Models\GalleryImageDescriptor;

class LocalGalleryService
{
    /**
     * @param string|null $slug
     * @return GalleryImageDescriptor[]
     */
    public function getLocalFiles(?string $slug = null): array
    {
        $galleryImage = GalleryImage::query();
        !is_null($slug) ? $galleryImage->where('slug', $slug) : null;

        return $galleryImage->get()->map(function ($item) {
            $tags = $item->tags->map(function ($tag) {
                return [
                    'id' => $tag->tag_id,
                    'value' => $tag->tag_value
                ];
            })->toArray();

            return new GalleryImageDescriptor(
                fileid: $item->fileid,
                href: $item->href,
                displayname: $item->displayname,
                tags: $tags,
                isFolder: false
            );
        })->toArray();
    }

}
