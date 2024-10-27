<?php

namespace App\Services;

use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GallerySyncResult;
use App\Utils\ArrayUtils;
use App\Utils\FileUtils;
use Exception;

class GallerySyncService
{
    private array $options;

    public function __construct(private readonly RemoteGalleryService $remoteGalleryService, private readonly LocalGalleryService $localGalleryService)
    {
        $this->options = [];
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function cleanGallery($name): void
    {
        $this->cleanGalleriesBySlug([FileUtils::createSlug($name)]);
    }

    public function syncGallery($name = null): void
    {
        try {
            $syncResult = $this->getSyncResult($name);
            echo $syncResult->toString();

            //$this->cleanGalleriesBySlug($gallerySlugs);

            foreach ($syncResult->filesToDownload as $file) {
                echo 'Processing file: ' . $file->displayname . "({$file->fileid})" . PHP_EOL;

                $galleryImage = GalleryImage::firstOrCreate([
                    'fileid' => $file->fileid
                ]);
                $galleryImage->displayname = $file->displayname;
                $galleryImage->href = $file->href;
                $galleryImage->name = $file->gallery['name'];
                $galleryImage->slug = $file->gallery['slug'];

                $galleryImage->tags()->sync(array_map(function ($tag) {
                    $galleryImageTag = GalleryImageTag::firstOrCreate([
                        'tag_id' => $tag['id']
                    ]);
                    $galleryImageTag['tag_value'] = $tag['value'];
                    $galleryImageTag->save();

                    return $galleryImageTag->tag_id;
                }, $file->tags));

                $galleryImage->save();

                if (isset($this->options['skip-download']) && $this->options['skip-download'] === true) {
                    $this->remoteGalleryService->downloadFile($file, $galleryImage->file_id);
                }
            }
        } catch (Exception $e) {
            echo 'Error syncing galleries: ' . $e->getMessage();
            echo $e->getTraceAsString();
        }
    }


    /**
     * @param array $galleries
     */
    private function cleanGalleriesBySlug(array $galleries)
    {
        foreach ($galleries as $gallery) {
            try {
                $galleryImage = GalleryImage::where('slug', $gallery)->get();
                $galleryImage->each(function ($image) {
                    $image->tags()->detach();
                    $image->delete();
                });
                FileUtils::removeDir(public_path(join(DIRECTORY_SEPARATOR, ["images", "gallery", $gallery])));
            } catch (Exception $e) {
                // Directory does not exist
            }
        }
    }

    /**
     * @param mixed $name
     * @return GallerySyncResult
     * @throws \Sabre\Xml\ParseException
     */
    public function getSyncResult(?string $name): GallerySyncResult
    {
        $slug = FileUtils::createSlug($name);

        $filesRemote = $this->remoteGalleryService->getRemoteFiles($name);
        $filesLocal = $this->localGalleryService->getLocalFiles($slug);

        $filesToDownload = ArrayUtils::subtract($filesRemote, $filesLocal);
        $tmp = ArrayUtils::subtract($filesRemote, $filesToDownload);
        $filesToRemove = ArrayUtils::subtract($filesLocal, $tmp);
        $filesUntouched = ArrayUtils::subtract($filesLocal, $filesToRemove);

        return new GallerySyncResult(
            $filesToDownload,
            $filesToRemove,
            $filesUntouched
        );
    }
}


