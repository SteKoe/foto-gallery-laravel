<?php

namespace Tests\Feature;

use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GalleryUser;
use App\Services\GallerySyncService;
use Illuminate\Support\Str;
use Tests\TestCase;

class GalleryAccessTest extends TestCase
{
    private const TAG_PUBLIC  = 9;
    private const TAG_FAMILY  = 1;
    private const TAG_FRIENDS = 2;
    private const TAG_PRIVATE = 3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(GallerySyncService::class);

        $this->createTags();
    }

    // ------------------------------------------------------------------
    // Anonymous access
    // ------------------------------------------------------------------

    public function test_anonymous_sees_image_with_only_public_tag(): void
    {
        [$image] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
        ]);

        $response = $this->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($image->file_id);
    }

    public function test_anonymous_does_not_see_image_with_public_and_other_tag(): void
    {
        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_PUBLIC, self::TAG_FAMILY]],
        ]);

        $response = $this->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    public function test_anonymous_does_not_see_image_without_public_tag(): void
    {
        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
        ]);

        $response = $this->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    public function test_anonymous_does_not_see_untagged_image(): void
    {
        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => []],
        ]);

        $response = $this->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    // ------------------------------------------------------------------
    // Authenticated access — subset match
    // ------------------------------------------------------------------

    public function test_authenticated_user_sees_images_where_all_tags_granted(): void
    {
        $user = $this->createUser('user-token', [
            self::TAG_FAMILY  => null,
            self::TAG_FRIENDS => null,
        ]);

        [$imgA, $imgB, $imgC, $imgD, $imgE] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_FAMILY]],
            ['tags' => [self::TAG_FRIENDS]],
            ['tags' => [self::TAG_FAMILY, self::TAG_FRIENDS]],
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_PUBLIC, self::TAG_FAMILY]],
        ]);

        $response = $this->withGalleryToken('user-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($imgA->file_id);
        $response->assertSee($imgB->file_id);
        $response->assertSee($imgC->file_id);
        $response->assertSee($imgD->file_id);
        $response->assertSee($imgE->file_id);
    }

    public function test_authenticated_user_does_not_see_images_with_ungranted_tags(): void
    {
        $user = $this->createUser('user-token', [
            self::TAG_FAMILY  => null,
            self::TAG_FRIENDS => null,
        ]);

        [$visible, $hiddenA, $hiddenB, $hiddenC] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_PRIVATE]],
            ['tags' => [self::TAG_FAMILY, self::TAG_PRIVATE]],
            ['tags' => [self::TAG_PUBLIC, self::TAG_PRIVATE]],
        ]);

        $response = $this->withGalleryToken('user-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hiddenA->file_id);
        $response->assertDontSee($hiddenB->file_id);
        $response->assertDontSee($hiddenC->file_id);
    }

    public function test_user_with_public_tag_sees_everything(): void
    {
        $user = $this->createUser('admin-token', [
            self::TAG_PUBLIC => null,
        ]);

        [$imgPublic, $imgFamily, $imgMixed, $imgPrivate, $imgUntagged] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
            ['tags' => [self::TAG_PUBLIC, self::TAG_FAMILY]],
            ['tags' => [self::TAG_PRIVATE]],
            ['tags' => []],
        ]);

        $response = $this->withGalleryToken('admin-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($imgPublic->file_id);
        $response->assertSee($imgFamily->file_id);
        $response->assertSee($imgMixed->file_id);
        $response->assertSee($imgPrivate->file_id);
        $response->assertSee($imgUntagged->file_id);
    }

    public function test_user_with_no_tags_matches_anonymous(): void
    {
        $user = $this->createUser('empty-token', []);

        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
        ]);

        $response = $this->withGalleryToken('empty-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    public function test_authenticated_user_does_not_see_untagged_image(): void
    {
        $user = $this->createUser('user-token', [
            self::TAG_FAMILY => null,
        ]);

        [$visible, $untagged] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_FAMILY]],
            ['tags' => []],
        ]);

        $response = $this->withGalleryToken('user-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($untagged->file_id);
    }

    // ------------------------------------------------------------------
    // Index page
    // ------------------------------------------------------------------

    public function test_anonymous_index_shows_only_galleries_with_visible_images(): void
    {
        $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
        ]);

        $this->setupGallery('gallery-b', 'Gallery B', [
            ['tags' => [self::TAG_FAMILY]],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/gallery/gallery-a');
        $response->assertDontSee('/gallery/gallery-b');
    }

    // ------------------------------------------------------------------
    // Token delivery
    // ------------------------------------------------------------------

    public function test_token_via_query_param_authenticates_user(): void
    {
        $user = $this->createUser('query-token', [
            self::TAG_FAMILY => null,
        ]);

        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_FAMILY]],
            ['tags' => [self::TAG_PRIVATE]],
        ]);

        $response = $this->get('/gallery/gallery-a?token=query-token');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    public function test_token_via_cookie_authenticates_user(): void
    {
        $user = $this->createUser('cookie-token', [
            self::TAG_FAMILY => null,
        ]);

        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_FAMILY]],
            ['tags' => [self::TAG_PRIVATE]],
        ]);

        $response = $this->withCookie('token', 'cookie-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    public function test_invalid_token_falls_back_to_anonymous(): void
    {
        [$visible, $hidden] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
        ]);

        $response = $this->get('/gallery/gallery-a?token=nonexistent');

        $response->assertStatus(200);
        $response->assertSee($visible->file_id);
        $response->assertDontSee($hidden->file_id);
    }

    // ------------------------------------------------------------------
    // Scope filtering
    // ------------------------------------------------------------------

    public function test_scoped_tag_only_applies_on_matching_gallery(): void
    {
        $user = $this->createUser('scoped-token', [
            self::TAG_FAMILY => 'gallery-a',
        ]);

        [$aPublic, $aFamily] = $this->setupGallery('gallery-a', 'Gallery A', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
        ]);

        [$bPublic, $bFamily] = $this->setupGallery('gallery-b', 'Gallery B', [
            ['tags' => [self::TAG_PUBLIC]],
            ['tags' => [self::TAG_FAMILY]],
        ]);

        // On gallery-a: user has family tag (scope matches) + public → sees both
        $response = $this->withGalleryToken('scoped-token')->get('/gallery/gallery-a');

        $response->assertStatus(200);
        $response->assertSee($aPublic->file_id);
        $response->assertSee($aFamily->file_id);

        // On gallery-b: user's family tag is scope-filtered out → only public visible
        $response = $this->withGalleryToken('scoped-token')->get('/gallery/gallery-b');

        $response->assertStatus(200);
        $response->assertSee($bPublic->file_id);
        $response->assertDontSee($bFamily->file_id);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function createTags(): void
    {
        GalleryImageTag::create(['tag_id' => self::TAG_PUBLIC,  'tag_value' => 'public']);
        GalleryImageTag::create(['tag_id' => self::TAG_FAMILY,  'tag_value' => 'family']);
        GalleryImageTag::create(['tag_id' => self::TAG_FRIENDS, 'tag_value' => 'friends']);
        GalleryImageTag::create(['tag_id' => self::TAG_PRIVATE, 'tag_value' => 'private']);
    }

    /**
     * @return GalleryImage[]
     */
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

    /**
     * @param array<int, string|null> $tagsWithScope  tag_id => scope (null for global)
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

    /**
     * Convenience: authenticate via token cookie.
     */
    private function withGalleryToken(string $token): static
    {
        return $this->withCookie('token', $token);
    }
}
