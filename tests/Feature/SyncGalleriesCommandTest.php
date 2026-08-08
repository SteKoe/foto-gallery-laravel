<?php

namespace Tests\Feature;

use App\Models\GalleryImageDescriptor;
use App\Models\GallerySyncResult;
use App\Services\GallerySyncService;
use Mockery;
use Tests\TestCase;

class SyncGalleriesCommandTest extends TestCase
{
    private GallerySyncService|Mockery\MockInterface $syncServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->syncServiceMock = Mockery::mock(GallerySyncService::class);
        $this->app->instance(GallerySyncService::class, $this->syncServiceMock);
    }

    public function test_syncs_named_gallery_with_files_to_download(): void
    {
        $result = $this->makeSyncResult(
            [$this->makeDescriptor('IMG_0001.JPG')],
            [],
            []
        );

        $this->syncServiceMock->shouldReceive('setOptions')->once()->with([]);
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->with('2024.08 Perseiden')
            ->andReturn($result);

        $this->artisan('gallery:sync', ['--name' => '2024.08 Perseiden'])
            ->expectsOutput('Sync completed successfully!')
            ->assertSuccessful();
    }

    public function test_syncs_all_galleries_with_files_to_remove(): void
    {
        $result = $this->makeSyncResult(
            [],
            [$this->makeDescriptor('IMG_9999.JPG')],
            []
        );

        $this->syncServiceMock->shouldReceive('setOptions')->once()->with([]);
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->with(null)
            ->andReturn($result);

        $this->artisan('gallery:sync')
            ->expectsOutput('Sync completed successfully!')
            ->assertSuccessful();
    }

    public function test_reports_up_to_date_when_no_changes(): void
    {
        $result = $this->makeSyncResult([], [], []);

        $this->syncServiceMock->shouldReceive('setOptions')->once()->with([]);
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->with(null)
            ->andReturn($result);

        $this->artisan('gallery:sync')
            ->expectsOutput('Gallery is already up to date!')
            ->assertSuccessful();
    }

    public function test_returns_failure_when_sync_returns_null(): void
    {
        $this->syncServiceMock->shouldReceive('setOptions');
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->andReturn(null);

        $this->artisan('gallery:sync')
            ->expectsOutput('Error syncing galleries. Please check the logs for more details.')
            ->assertFailed();
    }

    public function test_returns_failure_on_exception(): void
    {
        $this->syncServiceMock->shouldReceive('setOptions');
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->andThrow(new \Exception('Disk full'));

        $this->artisan('gallery:sync')
            ->expectsOutput('Disk full')
            ->assertFailed();
    }

    public function test_skip_download_option_passed_to_service(): void
    {
        $result = $this->makeSyncResult([], [], []);

        $this->syncServiceMock->shouldReceive('setOptions')->once()->with(['skip-download' => true]);
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->andReturn($result);

        $this->artisan('gallery:sync', ['--skip-download' => true])
            ->expectsOutput('Gallery is already up to date!')
            ->assertSuccessful();
    }

    public function test_force_option_passed_to_service(): void
    {
        $result = $this->makeSyncResult([], [], []);

        $this->syncServiceMock->shouldReceive('setOptions')->once()->with(['force' => true]);
        $this->syncServiceMock
            ->shouldReceive('syncGallery')
            ->once()
            ->andReturn($result);

        $this->artisan('gallery:sync', ['--force' => true])
            ->expectsOutput('Gallery is already up to date!')
            ->assertSuccessful();
    }

    private function makeSyncResult(
        array $download = [],
        array $remove = [],
        array $untouched = []
    ): GallerySyncResult {
        return new GallerySyncResult($download, $remove, $untouched);
    }

    private function makeDescriptor(string $displayname = 'IMG_0001.JPG'): GalleryImageDescriptor
    {
        return new GalleryImageDescriptor(
            fileid: '12345',
            href: '/remote.php/dav/files/user/Bilder/2024/2024.08%20Perseiden/'.$displayname,
            displayname: $displayname,
            tags: [],
            gallery: ['slug' => '2024.08-perseiden', 'name' => '2024.08 Perseiden'],
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
