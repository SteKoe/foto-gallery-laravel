<?php

namespace App\Services;

use App\Models\GalleryGuesser;
use App\Models\GalleryImageDescriptor;
use App\Models\GuessGalleryByDateInHref;
use App\Models\GuessGalleryWithoutDateInHref;
use App\Utils\ArrayUtils;
use Exception;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Sabre\DAV\Client;
use Sabre\Xml\Service;

class RemoteGalleryService
{
    private Client $client;
    private Service $service;
    private string $baseUri;
    /**
     * @var GalleryGuesser[]
     */
    private array $galleryGuesser;

    public function __construct()
    {
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

    /**
     * @param mixed $name
     * @return GalleryImageDescriptor[]
     * @throws \Sabre\Xml\ParseException
     * @throws Exception
     */
    public function getRemoteFiles(?string $name): array
    {
        $body = '
                <oc:filter-files xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns" xmlns:ocs="http://open-collaboration-services.org/ns">
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
        $output = array_map(function ($item) {
            return $this->mapDavResponse($item);
        }, $parsedXml);

        $filteredFiles = array_values(array_filter($output, function ($item) use ($name) {
            return str_contains(strtolower($item->href), strtolower(rawurlencode($name))) && !$item->isFolder;
        }));

        $folders = array_values(array_filter($output, function ($item) use ($name) {
            return str_contains(strtolower($item->href), strtolower(rawurlencode($name))) && $item->isFolder;
        }));

        $folderContent = ArrayUtils::flatMap(function ($folder) {
            $body = '
                    <d:propfind xmlns:d="DAV:" xmlns:nc="http://nextcloud.org/ns" xmlns:oc="http://owncloud.org/ns" xmlns:ocs="http://open-collaboration-services.org/ns">
                        <d:prop>
                            <d:displayname/>
                            <d:getcontenttype/>
                            <nc:system-tags/>
                            <oc:fileid/>
                        </d:prop>
                    </d:propfind>
                ';

            $href = str_replace("/remote.php/dav/files/", "", $folder->href);

            $response = $this->client->request('PROPFIND', $href, $body, [
                'headers' => [
                    'Content-Type' => 'application/xml',
                    'Depth' => 1,
                ]
            ]);

            $parsedXml = $this->service->parse($response['body']);
            return array_map(function ($item) {
                $mapDavResponse = $this->mapDavResponse($item);
                $mapDavResponse->tags[] = ['id' => 9, 'value' => 'gallery'];
                return $mapDavResponse;
            }, $parsedXml);
        }, $folders);

        return array_values(array_filter(array_merge($filteredFiles, $folderContent), function ($item) {
            return preg_match('/\.(jpg|jpeg|png|gif|webm)$/i', $item->href);
        }));
    }

    public function mapDavResponse($item): GalleryImageDescriptor
    {
        $mappedDavResponse = $this->parseDavResponse($item);
        $pathinfo = pathinfo($mappedDavResponse->href);

        $re = '/(\d{4})\/(.*)/m';
        preg_match_all($re, $pathinfo['dirname'], $matches, PREG_SET_ORDER, 0);

        /**
         * @var GalleryGuesser[] $a
         */
        $a = array_values(array_filter($this->galleryGuesser, function (GalleryGuesser $guesser) use ($mappedDavResponse) {
            return $guesser->accept($mappedDavResponse->href);
        }));

        try {
            $gallery = $a[0]->getGallery($mappedDavResponse->href);
            $section = $a[0]->getSection($mappedDavResponse->href);

            $mappedDavResponse->gallery = $gallery;
            $mappedDavResponse->section = $section;
            return $mappedDavResponse;
        } catch (Exception $e) {
            return $mappedDavResponse;
        }
    }

    /**
     * @param $davResponse
     * @return GalleryImageDescriptor
     */
    private function parseDavResponse($davResponse): GalleryImageDescriptor
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
                            } elseif ($prop['name'] === '{http://nextcloud.org/ns}system-tags' && !is_null($prop['value'])) {
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

        return new GalleryImageDescriptor(
            fileid: $result['fileid'],
            href: $result['href'],
            displayname: $result['displayname'],
            tags: $result['tags'],
            isFolder: $result['isFolder']
        );
    }

    public function downloadFile(GalleryImageDescriptor $file, $file_id, $dimensions = [1024, 1024])
    {
        try {
            $pathInfo = pathinfo($file->href);
            $file_name = "{$file_id}.{$pathInfo['extension']}";
            $storagePath = storage_path("app/public");
            $outputPath = join(DIRECTORY_SEPARATOR, [$storagePath, "gallery", $file->gallery['slug'], $file_name]);
            $targetDir = join(DIRECTORY_SEPARATOR, [$storagePath, "gallery", $file->gallery['slug']]);

            echo "Generating thumbnail for $outputPath .... ";

            if (file_exists($outputPath)) {
                echo "File already exists, skipping download.\n";
                return null;
            }

            $response = $this->client->request('GET', $file->href);

            try {
                mkdir($targetDir, 0777, true);
            } catch (Exception $e) {
                // Directory already exists
            }

            [$width, $height] = $dimensions;

            $imageManager = new ImageManager(new Driver());
            $dst = $imageManager->decode($response['body']);
            $dst->core()->native()->stripImage();
            $dst->scale($width, $height);
            $dst->encode(new JpegEncoder(90))->save($outputPath);
            unset($imageManager, $dst, $response);
            echo "done!\n";
        } catch (Exception $e) {
            echo 'Error downloading and processing file: ' . $e->getMessage();
        }
    }

}
