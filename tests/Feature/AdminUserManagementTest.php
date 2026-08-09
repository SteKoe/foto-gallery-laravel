<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GalleryUser;
use App\Services\GallerySyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    private const TAG_PUBLIC  = 9;
    private const TAG_FAMILY  = 1;
    private const TAG_FRIENDS = 2;

    private GalleryUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(GallerySyncService::class);

        $this->createTags();

        $this->admin = $this->createUser('admin-token', [], true);
    }

    // ------------------------------------------------------------------
    // Create user
    // ------------------------------------------------------------------

    public function test_admin_can_create_user(): void
    {
        $response = $this->adminPost('/admin/user', [
            'user_token' => 'new-user-token',
        ]);

        $user = GalleryUser::where('token', 'new-user-token')->first();
        $this->assertNotNull($user);
        $response->assertRedirect(route('admin.user', ['user_id' => $user->user_id]));
        $this->assertFalse($user->is_admin);
    }

    public function test_admin_can_create_admin_user(): void
    {
        $this->adminPost('/admin/user', [
            'user_token' => 'new-admin-token',
            'is_admin' => '1',
        ]);

        $user = GalleryUser::where('token', 'new-admin-token')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_admin);
    }

    // ------------------------------------------------------------------
    // Update user
    // ------------------------------------------------------------------

    public function test_admin_can_update_user_token(): void
    {
        $user = $this->createUser('old-token', []);

        $response = $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'updated-token',
        ]);

        $response->assertRedirect(route('admin.user', ['user_id' => $user->user_id]));

        $user->refresh();
        $this->assertEquals('updated-token', $user->token);
    }

    public function test_admin_can_update_user_is_admin_flag(): void
    {
        $user = $this->createUser('regular-token', []);
        $this->assertFalse($user->is_admin);

        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'regular-token',
            'is_admin' => '1',
        ]);

        $user->refresh();
        $this->assertTrue($user->is_admin);

        // Remove admin
        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'regular-token',
        ]);

        $user->refresh();
        $this->assertFalse($user->is_admin);
    }

    public function test_admin_can_assign_global_tags_to_user(): void
    {
        $user = $this->createUser('tagged-user', []);

        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'tagged-user',
            'tag' => [
                'global' => [self::TAG_FAMILY, self::TAG_FRIENDS],
            ],
        ]);

        $tagIds = DB::table('gallery_users_gallery_image_tags')
            ->where('user_id', $user->user_id)
            ->pluck('tag_id')
            ->sort()
            ->values()
            ->toArray();
        $this->assertEquals([self::TAG_FAMILY, self::TAG_FRIENDS], $tagIds);

        $pivot = DB::table('gallery_users_gallery_image_tags')
            ->where('user_id', $user->user_id)
            ->where('tag_id', self::TAG_FAMILY)
            ->first();
        $this->assertEquals('global', $pivot->scope);
    }

    public function test_admin_can_assign_scoped_tags_to_user(): void
    {
        $this->setupGallery('gallery-a', 'Gallery A', []);

        $user = $this->createUser('scoped-user', []);

        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'scoped-user',
            'tag' => [
                'gallery-a' => [self::TAG_FAMILY],
            ],
        ]);

        $pivot = DB::table('gallery_users_gallery_image_tags')
            ->where('user_id', $user->user_id)
            ->where('tag_id', self::TAG_FAMILY)
            ->first();

        $this->assertNotNull($pivot);
        $this->assertEquals('gallery-a', $pivot->scope);
    }

    public function test_updating_user_replaces_old_tags(): void
    {
        $user = $this->createUser('replace-user', [
            self::TAG_FAMILY  => 'global',
            self::TAG_FRIENDS => 'global',
        ]);

        $this->assertEquals(2, $user->tags()->count());

        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'replace-user',
            'tag' => [
                'global' => [self::TAG_FAMILY],
            ],
        ]);

        $user->refresh();
        $this->assertEquals(1, $user->tags()->count());
        $this->assertEquals(self::TAG_FAMILY, $user->tags()->first()->tag_id);
    }

    public function test_updating_user_without_tags_clears_all_tags(): void
    {
        $user = $this->createUser('clear-user', [
            self::TAG_FAMILY  => 'global',
            self::TAG_FRIENDS => 'global',
        ]);

        $this->assertEquals(2, $user->tags()->count());

        $this->adminPost("/admin/user/{$user->user_id}", [
            'user_token' => 'clear-user',
        ]);

        $user->refresh();
        $this->assertEquals(0, $user->tags()->count());
    }

    // ------------------------------------------------------------------
    // Delete user
    // ------------------------------------------------------------------

    public function test_admin_can_delete_user(): void
    {
        $user = $this->createUser('doomed-user', []);
        $userId = $user->user_id;

        $response = $this->adminPost("/admin/user/{$userId}/delete");

        $response->assertRedirect(route('admin.users'));
        $this->assertDatabaseMissing('gallery_users', ['user_id' => $userId]);
    }

    public function test_deleting_user_removes_tag_pivot(): void
    {
        $user = $this->createUser('pivot-user', [
            self::TAG_FAMILY  => 'global',
            self::TAG_FRIENDS => 'gallery-a',
        ]);

        $this->assertEquals(2, $user->tags()->count());

        $this->adminPost("/admin/user/{$user->user_id}/delete");

        $this->assertDatabaseMissing('gallery_users_gallery_image_tags', ['user_id' => $user->user_id]);
    }

    // ------------------------------------------------------------------
    // Access control for admin routes
    // ------------------------------------------------------------------

    public function test_anonymous_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/');
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = $this->createUser('regular-user', []);

        $response = $this->withGalleryToken('regular-user')->get('/admin');

        $response->assertRedirect('/');
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $this->createUser('regular-user', []);

        $this->postAs('regular-user', '/admin/user', ['user_token' => 'hijacked-token'])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('gallery_users', ['token' => 'hijacked-token']);
    }

    public function test_non_admin_cannot_delete_user(): void
    {
        $target = $this->createUser('target-user', []);
        $this->createUser('regular-user', []);

        $this->postAs('regular-user', "/admin/user/{$target->user_id}/delete")
            ->assertRedirect('/');

        $this->assertDatabaseHas('gallery_users', ['user_id' => $target->user_id]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function createTags(): void
    {
        GalleryImageTag::create(['tag_id' => self::TAG_PUBLIC,  'tag_value' => 'public']);
        GalleryImageTag::create(['tag_id' => self::TAG_FAMILY,  'tag_value' => 'family']);
        GalleryImageTag::create(['tag_id' => self::TAG_FRIENDS, 'tag_value' => 'friends']);
    }

    /**
     * @param array<int, string|null> $tagsWithScope  tag_id => scope
     */
    private function createUser(string $token, array $tagsWithScope = [], bool $isAdmin = false): GalleryUser
    {
        $user = GalleryUser::create([
            'token' => $token,
            'is_admin' => $isAdmin,
        ]);

        foreach ($tagsWithScope as $tagId => $scope) {
            $user->tags()->attach($tagId, [
                'id' => Str::uuid()->toString(),
                'scope' => $scope,
            ]);
        }

        return $user->fresh();
    }

    private function setupGallery(string $slug, string $name, array $imageDefs): array
    {
        $images = [];
        $idx = 0;

        foreach ($imageDefs as $def) {
            $idx++;
            $fileId = Str::uuid()->toString();

            $image = new GalleryImage();
            $image->file_id = $fileId;
            $image->fileid = $fileId;
            $image->displayname = sprintf('IMG_%04d', $idx);
            $image->href = "https://cloud.example.com/path/{$slug}/{$fileId}.jpg";
            $image->name = $name;
            $image->slug = $slug;
            $image->save();

            if (!empty($def['tags'])) {
                $image->tags()->attach($def['tags']);
            }

            $images[] = $image->fresh();
        }

        return $images;
    }

    private function withGalleryToken(string $token): static
    {
        return $this->withCookie('token', $token);
    }

    private function adminPost(string $uri, array $data = [], string $token = 'admin-token'): \Illuminate\Testing\TestResponse
    {
        return $this->postAs($token, $uri, $data);
    }

    private function postAs(string $token, string $uri, array $data = []): \Illuminate\Testing\TestResponse
    {
        $csrf = Str::random(40);

        return $this->withSession(['_token' => $csrf])
            ->withCookie('token', $token)
            ->post($uri, array_merge(['_token' => $csrf], $data));
    }
}
