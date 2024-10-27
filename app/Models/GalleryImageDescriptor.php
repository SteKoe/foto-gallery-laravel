<?php

namespace App\Models;

class GalleryImageDescriptor
{
    public readonly ?string $fileid;
    public readonly string $href;
    public readonly string $displayname;
    public readonly bool $isFolder;
    public array $tags;
    public array $gallery;
    public ?string $section;

    public function __construct(?string $fileid, string $href, string $displayname, array $tags, bool $isFolder = false, array $gallery = [], string $section = null)
    {
        $this->fileid = $fileid;
        $this->href = $href;
        $this->displayname = $displayname;
        $this->tags = $tags;
        $this->isFolder = $isFolder;
        $this->gallery = $gallery;
        $this->section = $section;
    }
}
