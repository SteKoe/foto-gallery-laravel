<?php

namespace Tests\Feature;

use App\Services\GallerySyncService;
use Mockery;
use Tests\TestCase;

class CleanGalleriesCommandTest extends TestCase
{
    private GallerySyncService|Mockery\MockInterface $syncServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->syncServiceMock = Mockery::mock(GallerySyncService::class);
        $this->app->instance(GallerySyncService::class, $this->syncServiceMock);
    }

    public function test_aborts_when_confirmation_denied(): void
    {
        $this->syncServiceMock
            ->shouldNotReceive('cleanGallery');

        $this->artisan('gallery:clean', ['--name' => '2024.08 Unsere Hochzeit'])
            ->expectsConfirmation('Do you really want to delete gallery "2024.08 Unsere Hochzeit" from local storage?', 'no')
            ->expectsOutput('Aborted.')
            ->assertSuccessful();
    }

    public function test_cleans_named_gallery_when_confirmed(): void
    {
        $this->syncServiceMock
            ->shouldReceive('cleanGallery')
            ->once()
            ->with('2024.08 Unsere Hochzeit');

        $this->artisan('gallery:clean', ['--name' => '2024.08 Unsere Hochzeit'])
            ->expectsConfirmation('Do you really want to delete gallery "2024.08 Unsere Hochzeit" from local storage?', 'yes')
            ->expectsOutput('Clean completed successfully!')
            ->assertSuccessful();
    }

    public function test_cleans_all_galleries_when_confirmed(): void
    {
        $this->syncServiceMock
            ->shouldReceive('cleanGallery')
            ->once()
            ->with(null);

        $this->artisan('gallery:clean')
            ->expectsConfirmation('Do you really want to delete gallery "All" from local storage?', 'yes')
            ->expectsOutput('Clean completed successfully!')
            ->assertSuccessful();
    }

    public function test_skips_confirmation_with_force_flag(): void
    {
        $this->syncServiceMock
            ->shouldReceive('cleanGallery')
            ->once()
            ->with('2024.08 Unsere Hochzeit');

        $this->artisan('gallery:clean', ['--name' => '2024.08 Unsere Hochzeit', '--force' => true])
            ->doesntExpectOutput('Aborted.')
            ->expectsOutput('Clean completed successfully!')
            ->assertSuccessful();
    }

    public function test_returns_failure_on_exception(): void
    {
        $this->syncServiceMock
            ->shouldReceive('cleanGallery')
            ->andThrow(new \Exception('Something went wrong'));

        $this->artisan('gallery:clean', ['--name' => 'BrokenGallery', '--force' => true])
            ->expectsOutput('Something went wrong')
            ->assertFailed();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
