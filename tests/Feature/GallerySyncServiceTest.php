<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\GalleryImageDescriptor;
use App\Models\GalleryImageTag;
use App\Services\GallerySyncService;
use App\Services\LocalGalleryService;
use App\Services\RemoteGalleryService;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class GallerySyncServiceTest extends TestCase
{
    private const TAG_PUBLIC  = 9;
    private const TAG_FAMILY  = 1;
    private const TAG_FRIENDS = 2;

    private RemoteGalleryService|Mockery\MockInterface $remoteMock;
    private LocalGalleryService|Mockery\MockInterface $localMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->remoteMock = Mockery::mock(RemoteGalleryService::class);
        $this->localMock = Mockery::mock(LocalGalleryService::class);

        $this->app->instance(RemoteGalleryService::class, $this->remoteMock);
        $this->app->instance(LocalGalleryService::class, $this->localMock);
    }

    public function test_sync_syncs_tags_for_already_synced_images(): void
    {
        // Existing local image with only TAG_PUBLIC
        $fileId = 'remote-file-001';
        $image = $this->createLocalImage($fileId, 'gallery-a', 'Gallery A', [
            ['id' => self::TAG_PUBLIC, 'value' => 'public'],
        ]);

        // Remote: same file, but now also tagged TAG_FAMILY
        $remoteDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-a',
            'Gallery A',
            [
                ['id' => self::TAG_PUBLIC, 'value' => 'public'],
                ['id' => self::TAG_FAMILY, 'value' => 'family'],
            ]
        );

        // Local service reports the existing file (with old tags)
        $localDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-a',
            'Gallery A',
            [['id' => self::TAG_PUBLIC, 'value' => 'public']]
        );

        $this->remoteMock->shouldReceive('getRemoteFiles')->with('gallery-a')->andReturn([$remoteDescriptor]);
        $this->localMock->shouldReceive('getLocalFiles')->with('gallery-a')->andReturn([$localDescriptor]);
        $this->remoteMock->shouldNotReceive('downloadFile');

        $service = new GallerySyncService($this->remoteMock, $this->localMock);
        $service->setOptions(['skip-download' => true]);

        $result = $service->syncGallery('gallery-a');

        $this->assertNotNull($result);
        $this->assertCount(0, $result->filesToDownload);
        $this->assertCount(0, $result->filesToRemove);
        $this->assertCount(1, $result->filesUntouched);

        // Assert the image now has both tags
        $image->refresh();
        $tagIds = $image->tags()->pluck('gallery_image_tags.tag_id')->toArray();
        $this->assertContains(self::TAG_PUBLIC, $tagIds);
        $this->assertContains(self::TAG_FAMILY, $tagIds);
    }

    public function test_sync_removes_tags_no_longer_on_remote(): void
    {
        $fileId = 'remote-file-002';
        $image = $this->createLocalImage($fileId, 'gallery-b', 'Gallery B', [
            ['id' => self::TAG_PUBLIC, 'value' => 'public'],
            ['id' => self::TAG_FAMILY, 'value' => 'family'],
        ]);

        // Remote: same file, but TAG_FAMILY removed
        $remoteDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-b',
            'Gallery B',
            [['id' => self::TAG_PUBLIC, 'value' => 'public']]
        );

        $localDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-b',
            'Gallery B',
            [
                ['id' => self::TAG_PUBLIC, 'value' => 'public'],
                ['id' => self::TAG_FAMILY, 'value' => 'family'],
            ]
        );

        $this->remoteMock->shouldReceive('getRemoteFiles')->with('gallery-b')->andReturn([$remoteDescriptor]);
        $this->localMock->shouldReceive('getLocalFiles')->with('gallery-b')->andReturn([$localDescriptor]);
        $this->remoteMock->shouldNotReceive('downloadFile');

        $service = new GallerySyncService($this->remoteMock, $this->localMock);
        $service->setOptions(['skip-download' => true]);

        $service->syncGallery('gallery-b');

        $image->refresh();
        $tagIds = $image->tags()->pluck('gallery_image_tags.tag_id')->toArray();
        $this->assertContains(self::TAG_PUBLIC, $tagIds);
        $this->assertNotContains(self::TAG_FAMILY, $tagIds);
    }

    public function test_sync_updates_tag_value_when_changed_on_remote(): void
    {
        $fileId = 'remote-file-003';
        $image = $this->createLocalImage($fileId, 'gallery-c', 'Gallery C', [
            ['id' => self::TAG_FAMILY, 'value' => 'family'],
        ]);

        // Remote: same tag id but different value
        $remoteDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-c',
            'Gallery C',
            [['id' => self::TAG_FAMILY, 'value' => 'renamed-tag']]
        );

        $localDescriptor = $this->makeDescriptor(
            $fileId,
            'gallery-c',
            'Gallery C',
            [['id' => self::TAG_FAMILY, 'value' => 'family']]
        );

        $this->remoteMock->shouldReceive('getRemoteFiles')->with('gallery-c')->andReturn([$remoteDescriptor]);
        $this->localMock->shouldReceive('getLocalFiles')->with('gallery-c')->andReturn([$localDescriptor]);
        $this->remoteMock->shouldNotReceive('downloadFile');

        $service = new GallerySyncService($this->remoteMock, $this->localMock);
        $service->setOptions(['skip-download' => true]);

        $service->syncGallery('gallery-c');

        $tag = GalleryImageTag::find(self::TAG_FAMILY);
        $this->assertSame('renamed-tag', $tag->tag_value);
    }

    public function test_sync_downloads_new_files_and_syncs_tags_for_existing(): void
    {
        // Existing local image
        $existingFileId = 'remote-file-004';
        $image = $this->createLocalImage($existingFileId, 'gallery-d', 'Gallery D', [
            ['id' => self::TAG_PUBLIC, 'value' => 'public'],
        ]);

        // New remote file
        $newFileId = 'remote-file-005';
        $newDescriptor = $this->makeDescriptor(
            $newFileId,
            'gallery-d',
            'Gallery D',
            [['id' => self::TAG_PUBLIC, 'value' => 'public']]
        );

        // Existing remote file with added tag
        $existingRemoteDescriptor = $this->makeDescriptor(
            $existingFileId,
            'gallery-d',
            'Gallery D',
            [
                ['id' => self::TAG_PUBLIC, 'value' => 'public'],
                ['id' => self::TAG_FRIENDS, 'value' => 'friends'],
            ]
        );

        $localDescriptor = $this->makeDescriptor(
            $existingFileId,
            'gallery-d',
            'Gallery D',
            [['id' => self::TAG_PUBLIC, 'value' => 'public']]
        );

        $this->remoteMock->shouldReceive('getRemoteFiles')->with('gallery-d')
            ->andReturn([$newDescriptor, $existingRemoteDescriptor]);
        $this->localMock->shouldReceive('getLocalFiles')->with('gallery-d')
            ->andReturn([$localDescriptor]);
        $this->remoteMock->shouldReceive('downloadFile')->once();

        $service = new GallerySyncService($this->remoteMock, $this->localMock);

        $result = $service->syncGallery('gallery-d');

        $this->assertCount(1, $result->filesToDownload);
        $this->assertCount(1, $result->filesUntouched);

        // Existing image got the new tag
        $image->refresh();
        $tagIds = $image->tags()->pluck('gallery_image_tags.tag_id')->toArray();
        $this->assertContains(self::TAG_PUBLIC, $tagIds);
        $this->assertContains(self::TAG_FRIENDS, $tagIds);

        // New image was created
        $newImage = GalleryImage::where('fileid', $newFileId)->first();
        $this->assertNotNull($newImage);
    }

    private function createLocalImage(string $fileid, string $slug, string $name, array $tags): GalleryImage
    {
        $image = new GalleryImage();
        $image->file_id = Str::uuid()->toString();
        $image->fileid = $fileid;
        $image->displayname = 'IMG_0001';
        $image->href = "https://cloud.example.com/path/{$slug}/{$fileid}.jpg";
        $image->name = $name;
        $image->slug = $slug;
        $image->save();

        foreach ($tags as $tag) {
            GalleryImageTag::firstOrCreate(
                ['tag_id' => $tag['id']],
                ['tag_value' => $tag['value']]
            );
            $image->tags()->attach($tag['id']);
        }

        return $image->fresh();
    }

    private function makeDescriptor(string $fileid, string $slug, string $name, array $tags): GalleryImageDescriptor
    {
        return new GalleryImageDescriptor(
            fileid: $fileid,
            href: "/remote.php/dav/files/user/Bilder/{$slug}/{$fileid}.jpg",
            displayname: 'IMG_0001',
            tags: $tags,
            isFolder: false,
            gallery: ['slug' => $slug, 'name' => $name],
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
