<?php

namespace Tests\Unit;

include 'app/Services/GallerySyncService.php';

use App\Services\GallerySyncService;
use PHPUnit\Framework\TestCase;
use Tests\Data\Webdav\GalleryResponse;

class GallerySyncServiceTest extends TestCase
{
    private GallerySyncService $gallerySyncService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gallerySyncService = new GallerySyncService();
    }

    public function test_extractsGalleryImageInformationWithoutSubfolder()
    {
        $webdavGalleryResponse = GalleryResponse::year_title_image();
        $res = $this->gallerySyncService->mapDavResponse($webdavGalleryResponse);

        $this->assertEquals(array(
            'href' => '/remote.php/dav/files/stephan.koeninger/Bilder/2020/2020.08%20Perseiden/20200811-225920.JPG',
            'displayname' => '20200811-225920.JPG',
            'tags' => array(
                array(
                    'id' => '9',
                    'value' => 'Galerie'
                )
            ),
            'isFolder' => false,
            'slug' => '2020.08-perseiden',
            'name' => '2020.08 Perseiden',
            'section' => ''
        ), $res);
    }

    public function test_extractsGalleryImageInformationHavingSubfolder()
    {
        $webdavGalleryResponse = GalleryResponse::year_title_section_image();
        $res = $this->gallerySyncService->mapDavResponse($webdavGalleryResponse);

        $this->assertEquals(array(
            'href' => '/remote.php/dav/files/stephan.koeninger/Bilder/2020/2020.08%20Perseiden/01%20-%20Der%20Anfang/20200811-225920.JPG',
            'displayname' => '20200811-225920.JPG',
            'tags' => array(
                array(
                    'id' => '9',
                    'value' => 'Galerie'
                )
            ),
            'isFolder' => false,
            'slug' => '2020.08-perseiden',
            'name' => '2020.08 Perseiden',
            'section' => '01 - Der Anfang'
        ), $res);
    }
}
