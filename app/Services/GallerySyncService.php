<?php

namespace App\Services;

use App\Models\GalleryGuesser;
use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GuessGalleryByDateInHref;
use App\Models\GuessGalleryWithoutDateInHref;
use Exception;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sabre\DAV\Client;
use Sabre\Xml\Service;

class GallerySyncService
{
    private Client $client;
    private Service $service;
    private string $baseUri;
    private array $galleryGuesser;
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->galleryGuesser = [
            new GuessGalleryWithoutDateInHref(),
            new GuessGalleryByDateInHref()
        ];
        $this->service = new Service();
        $this->baseUri = env('WEBDAV_URL') . "/remote.php/dav/files/" . env('WEBDAV_USERNAME');
        $this->client = new Client([
            'baseUri' => $this->baseUri,
            'userName' => env('WEBDAV_USERNAME'),
            'password' => env('WEBDAV_PASSWORD'),
        ]);
    }

    public function cleanGallery($slug): void {
        $this->cleanGalleries([$slug]);
    }

    public function syncGallery($slug = null): void
    {
        try {
            $body = '
                <oc:filter-files  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns" xmlns:ocs="http://open-collaboration-services.org/ns">
                    <d:prop>
                        <d:displayname/>
                        <d:getcontenttype/>
                        <nc:system-tags/>
                        <oc:fileid/>
                    </d:prop>
                    <oc:filter-rules>
                        <oc:systemtag>9</oc:systemtag>
                    </oc:filter-rules>
                </oc:filter-files>
            ';

            $response = $this->client->request('REPORT', '', $body, [
                'headers' => [
                    'Content-Type' => 'application/xml',
                    'Depth' => 1,
                ]
            ]);

            if ($response['statusCode'] <= 200 || $response['statusCode'] >= 300) {
                throw new Exception('Failed to fetch data');
            }

            $parsedXml = $this->service->parse($response['body']);
            $output = array_map(function ($item) use ($slug) {
                return $this->mapDavResponse($item);
            }, $parsedXml);

            $filteredFiles = array_values(array_filter($output, function ($item) use ($slug) {
                return str_contains(strtolower($item['href']), strtolower(rawurlencode($slug))) && !$item['isFolder'];
            }));

            $galleries = array_unique(array_map(function ($file) {
                return $file['slug'];
            }, $filteredFiles));

            $this->cleanGalleries($galleries);

            foreach ($filteredFiles as $file) {
                $galleryImage = GalleryImage::firstOrCreate([
                    'fileid' => $file['fileid']
                ]);
                $galleryImage->displayname = $file['displayname'];
                $galleryImage->href = $file['href'];
                $galleryImage->name = $file['name'];
                $galleryImage->slug = $file['slug'];

                $galleryImage->tags()->sync(array_map(function ($tag) {
                    return GalleryImageTag::firstOrCreate([
                        'tag_id' => $tag['id'],
                        'tag_value' => $tag['value']
                    ])->tag_id;
                }, $file['tags']));

                $galleryImage->save();
            }

        } catch (Exception $e) {
            echo 'Error syncing galleries: ' . $e->getMessage();
        }
    }

    public function downloadFile($file, $file_id, $dimensions = [1024,1024]): ?ImageInterface
    {
        try {
            $pathInfo = pathinfo($file['href']);
            $file_name = "{$file_id}.{$pathInfo['extension']}";
            $outputPath = public_path(join(DIRECTORY_SEPARATOR, ["images", "gallery", $file['slug'], $file_name]));
            $targetDir = public_path(join(DIRECTORY_SEPARATOR, ["images", "gallery", $file['slug']]));

            echo "Generating thumbnail for $outputPath .... ";

            if (file_exists($outputPath)) {
                echo "File already exists, skipping download.\n";
                return null;
            }

            $response = $this->client->request('GET', $file['href']);

            try {
                mkdir($targetDir, 0777, true);
            } catch (Exception $e) {
                // Directory already exists
            }

            [$width, $height] = $dimensions;



            $dst = $this->imageManager->read($response['body']);
            $dst->scale($width, $height);
            $dst->toJpeg(90)->save($outputPath);

            $response = null;
            return $dst;

            echo "done!\n";

        } catch (Exception $e) {
            echo 'Error downloading and processing file: ' . $e->getMessage();
        }
    }

    public function mapDavResponse($item)
    {
        $mappedDavResponse = parseDavResponse($item);
        $pathinfo = pathinfo($mappedDavResponse['href']);


        $re = '/(\d{4})\/(.*)/m';
        preg_match_all($re, $pathinfo['dirname'], $matches, PREG_SET_ORDER, 0);

        $a = array_values(array_filter($this->galleryGuesser, function (GalleryGuesser $guesser) use ($mappedDavResponse) {
            return $guesser->accept($mappedDavResponse['href']);
        }));

        try {
            $gallery = $a[0]->getGallery($mappedDavResponse['href']);
            $section = $a[0]->getSection($mappedDavResponse['href']);

            return [
                ...$mappedDavResponse,
                ...$gallery,
                'section' => $section
            ];
        } catch (Exception $e) {
            return [
                ...$mappedDavResponse
            ];
        }
    }

    /**
     * @param array $galleries
     */
    private function cleanGalleries(array $galleries)
    {
        foreach ($galleries as $gallery) {
            try {
                $galleryImage = GalleryImage::where('slug', $gallery)->get();
                $galleryImage->each(function ($image) {
                    $image->tags()->detach();
                    $image->delete();
                });
                removeDir(public_path(join(DIRECTORY_SEPARATOR, ["images", "gallery", $gallery])));
            } catch (Exception $e) {
                // Directory does not exist
            }
        }
    }
}

function parseDavResponse($davResponse): array
{
    $result = [
        'fileid' => null,
        'href' => null,
        'displayname' => null,
        'tags' => [],
        'isFolder' => false
    ];

    // Loop through the response to find href, displayname, and tags
    foreach ($davResponse['value'] as $responseItem) {
        if ($responseItem['name'] === '{DAV:}href') {
            $result['href'] = $responseItem['value'];
        } elseif ($responseItem['name'] === '{DAV:}propstat') {
            foreach ($responseItem['value'] as $propstat) {
                if ($propstat['name'] === '{DAV:}prop') {
                    foreach ($propstat['value'] as $prop) {
                        if ($prop['name'] === '{http://owncloud.org/ns}fileid') {
                            $result['fileid'] = $prop['value'];
                        } elseif ($prop['name'] === '{DAV:}getcontenttype') {
                            $result['isFolder'] = $prop['value'] === null;
                        } elseif ($prop['name'] === '{DAV:}displayname') {
                            $result['displayname'] = $prop['value'];
                        } elseif ($prop['name'] === '{http://nextcloud.org/ns}system-tags') {
                            foreach ($prop['value'] as $tagItem) {
                                if ($tagItem['name'] === '{http://nextcloud.org/ns}system-tag') {
                                    $result['tags'][] = [
                                        'id' => $tagItem['attributes']['{http://owncloud.org/ns}id'],
                                        'value' => $tagItem['value']
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $result;
}

function removeDir(string $dir): void {
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it,
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) {
        if ($file->isDir()){
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($dir);
}
