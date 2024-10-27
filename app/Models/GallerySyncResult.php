<?php

namespace App\Models;

readonly class GallerySyncResult
{
    /**
     * @var GalleryImageDescriptor[]
     */
    public array $filesToDownload;
    /**
     * @var GalleryImageDescriptor[]
     */
    public array $filesToRemove;
    /**
     * @var GalleryImageDescriptor[]
     */
    public array $filesUntouched;

    /**
     * @param GalleryImageDescriptor[] $filesToDownload
     * @param GalleryImageDescriptor[] $filesToRemove
     * @param GalleryImageDescriptor[] $filesUntouched
     */
    public function __construct(array $filesToDownload, array $filesToRemove, array $filesUntouched)
    {
        $this->filesToDownload = $filesToDownload;
        $this->filesToRemove = $filesToRemove;
        $this->filesUntouched = $filesUntouched;
    }

    public function toString()
    {
        return sprintf(
            "Files to download: %d\nFiles to remove: %d\nFiles untouched: %d\n",
            count($this->filesToDownload),
            count($this->filesToRemove),
            count($this->filesUntouched)
        );
    }
}
