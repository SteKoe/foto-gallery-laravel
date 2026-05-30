<?php

namespace App\Services;

use App\Models\GalleryImage;
use App\Models\GalleryImageDescriptor;
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

    public function cleanGallery(?string $name = null): void
    {
        try {
            $filesToDelete = $this->localGalleryService->getLocalFiles($name);
            $this->deleteFiles($filesToDelete);
        } catch (Exception $e) {
            error_log('Error cleaning gallery: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            throw $e;
        }
    }

    public function syncGallery($name = null): ?GallerySyncResult
    {
        try {
            $syncResult = $this->getSyncResult($name);

            $this->deleteFiles($syncResult->filesToRemove);

            foreach ($syncResult->filesToDownload as $file) {
                $galleryImage = GalleryImage::firstOrCreate([
                    'fileid' => $file->fileid
                ]);
                $galleryImage->displayname = $file->displayname;
                $galleryImage->href = $file->href;
                $galleryImage->name = $file->gallery['name'];
                $galleryImage->slug = $file->gallery['slug'];

                $galleryImage->tags()->sync(array_map(function ($tag) {
                    $galleryImageTag = GalleryImageTag::firstOrCreate(
                        ['tag_id' => $tag['id']],
                        ['tag_value' => $tag['value']]
                    );

                    // Update tag_value in case it changed on the remote
                    if ($galleryImageTag->tag_value !== $tag['value']) {
                        $galleryImageTag->tag_value = $tag['value'];
                        $galleryImageTag->save();
                    }

                    return $galleryImageTag->tag_id;
                }, $file->tags));

                $galleryImage->save();

                if (!isset($this->options['skip-download']) || $this->options['skip-download'] !== true) {
                    $this->remoteGalleryService->downloadFile($file, $galleryImage->file_id);
                }
            }

            return $syncResult;
        } catch (Exception $e) {
            // Let controller handle returning an appropriate HTTP response; log and return null here
            error_log('Error syncing galleries: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            return null;
        }
    }


    /**
     * @param GalleryImageDescriptor[] $images
     */
    private function deleteFiles(array $images)
    {
        foreach ($images as $image) {
            try {
                $galleryImage = GalleryImage::where('fileid', $image->fileid)->get();
                $galleryImage->each(function ($image) {
                    $image->tags()->detach();
                    $image->delete();
                });
                FileUtils::removeDir(public_path(join(DIRECTORY_SEPARATOR, ["images", "gallery", $galleryImage->file_id])));
            } catch (Exception $e) {
                // Directory does not exist
            }
        }
    }

    public function listRemoteFiles()
    {
        $files = $this->remoteGalleryService->getRemoteFiles(null);
        $names = array_map(function ($galleryImageDescriptor) {
            return $galleryImageDescriptor->gallery['name'];
        }, $files);

        $unique = array_values(array_unique($names));
        natcasesort($unique);
        return $unique;
    }

    /**
     * @param mixed $name
     * @return GallerySyncResult
     * @throws \Sabre\Xml\ParseException
     */
    public function getSyncResult(?string $name): GallerySyncResult
    {
        $filesRemote = $this->remoteGalleryService->getRemoteFiles($name);
        $filesLocal = $this->localGalleryService->getLocalFiles($name);

        if ($this->getOption('force') === true) {
            $filesToDownload = $filesRemote;
        } else {
            $filesToDownload = ArrayUtils::subtract($filesRemote, $filesLocal);
        }

        $tmp = ArrayUtils::subtract($filesRemote, $filesToDownload);
        $filesToRemove = ArrayUtils::subtract($filesLocal, $tmp);
        $filesUntouched = ArrayUtils::subtract($filesLocal, $filesToRemove);

        return new GallerySyncResult(
            $filesToDownload,
            $filesToRemove,
            $filesUntouched
        );
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function getOption(string $key): mixed
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : null;
    }
}

