<?php

namespace App\Services;

use App\Models\GalleryImage;
use App\Models\GalleryImageDescriptor;
use App\Utils\FileUtils;

class LocalGalleryService
{
    /**
     * @param string|null $slug
     * @return GalleryImageDescriptor[]
     */
    public function getLocalFiles(?string $name = null): array
    {
        $slug = FileUtils::createSlug($name);

        $galleryImage = GalleryImage::query();
        if ($slug !== "") {
            $galleryImage = $galleryImage->where('slug', $slug);
        }

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
