<?php

namespace Tests\Feature;

use App\Services\GallerySyncService;
use Mockery;
use Tests\TestCase;

class ListGalleriesCommandTest extends TestCase
{
    private GallerySyncService|Mockery\MockInterface $syncServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->syncServiceMock = Mockery::mock(GallerySyncService::class);
        $this->app->instance(GallerySyncService::class, $this->syncServiceMock);
    }

    public function test_lists_remote_galleries(): void
    {
        $this->syncServiceMock->shouldReceive('setOptions');
        $this->syncServiceMock
            ->shouldReceive('listRemoteFiles')
            ->once()
            ->andReturn(['2024.08 Perseiden', '2020.08 Urlaub']);

        $this->artisan('gallery:list')
            ->expectsOutput('Available Remote Galleries:')
            ->expectsOutputToContain('2024.08 Perseiden')
            ->expectsOutputToContain('2020.08 Urlaub')
            ->expectsOutput('Total galleries: 2')
            ->assertSuccessful();
    }

    public function test_shows_message_when_no_galleries_found(): void
    {
        $this->syncServiceMock->shouldReceive('setOptions');
        $this->syncServiceMock
            ->shouldReceive('listRemoteFiles')
            ->once()
            ->andReturn([]);

        $this->artisan('gallery:list')
            ->expectsOutput('No galleries found on remote storage.')
            ->assertSuccessful();
    }

    public function test_returns_failure_on_exception(): void
    {
        $this->syncServiceMock->shouldReceive('setOptions');
        $this->syncServiceMock
            ->shouldReceive('listRemoteFiles')
            ->andThrow(new \Exception('Connection refused'));

        $this->artisan('gallery:list')
            ->expectsOutput('Connection refused')
            ->assertFailed();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
