<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Services\GallerySyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class GalleryController
{
    private GallerySyncService $gallerySyncService;

    public function __construct(GallerySyncService $gallerySyncService)
    {
        $this->gallerySyncService = $gallerySyncService;
    }

    function index()
    {
        $galleries = $this->getScopedGalleryImageQuery(session()->get('allowed_tags'))->get()->toArray();
        $grouped = array_reduce($galleries, function ($carry, $item) {
            $carry[$item['slug']][] = $item;
            return $carry;
        }, []);

        $grouped = array_map(function ($item) {
            $more_images = array_map(function ($item) {
                return $this->getImageSrc($item);
            }, array_slice($item, 1, 4));

            return [
                'file_id' => $item[0]['file_id'],
                'fileid' => $item[0]['fileid'],
                'displayname' => $item[0]['displayname'],
                'href' => $item[0]['href'],
                'name' => $item[0]['name'],
                'slug' => $item[0]['slug'],
                'more_images' => $more_images,
                'total_images' => count($item) - count($more_images),
            ];
        }, $grouped);

        $galleries = array_map(function ($gallery) {
            $gallery['cover'] = $this->getImageSrc($gallery);
            $gallery['name_no_date'] = preg_replace('/\d{4}(\.\d{2}){0,2}/', '', $gallery['name']);
            return $gallery;
        }, $grouped);

        krsort($galleries);

        return view('galleries', compact('galleries'));
    }

    function gallery(string $slug)
    {
        $images = $this->getScopedGalleryImageQuery(session()->get('allowed_tags'))->where('slug', $slug)->get();

        $name = $images[0]['name'];
        $images = $images->map($this->mapToImageResponse(), $images);
        return view('gallery', compact('slug', 'images', 'name'));
    }

    function image(string $id)
    {
        $path = $this->getGalleryImage($id);

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)->header('Content-Type', $type);
    }

    function logout(): RedirectResponse
    {
        session()->forget('allowed_tags');
        session()->forget('token');
        session()->forget('user');
        Cookie::queue(Cookie::forget('token'));

        return redirect()->route('home');
    }

    private function getScopedGalleryImageQuery($tags = [])
    {
        // When "tags" contains "*" do not filter at all.
        if (in_array('*', $tags)) {
            return GalleryImage::orderBy('displayname');
        } else {
            return GalleryImage::whereIn('file_id', function ($subquery) use ($tags) {
                $groupBy = $subquery->select('git.file_id')
                    ->from('gallery_image_gallery_image_tag as git')
                    ->groupBy('git.file_id');

                $groupBy
                    ->whereIn('git.tag_id', $tags)
                    ->whereNotIn('git.file_id', function ($subquery2) use ($tags) {
                        $subquery2->select('git.file_id')
                            ->from('gallery_image_gallery_image_tag as git')
                            ->whereNotIn('git.tag_id', $tags);
                    });
            })->orderBy('displayname');
        }
    }

    /**
     * @return \Closure<array<GalleryImage>>
     */
    public function mapToImageResponse(): \Closure
    {
        /**
         * @return array<GalleryImage>
         */
        return function ($image): array {
            $pathinfo = pathinfo($image['href']);
            $src = "{$image['file_id']}.{$pathinfo['extension']}";

            $fullPath = storage_path('app/public/gallery/' . $image['slug'] . '/' . $src);
            $imageSize = @getimagesize($fullPath);
            $width = $imageSize ? $imageSize[0] : 0;
            $height = $imageSize ? $imageSize[1] : 0;

            return array(
                "file_id" => $image['file_id'],
                "tags" => join(',', array_map(function ($image) {
                    return $image['tag_value'];
                }, $image->tags()->get()->toArray())),
                "src" => $src,
                "size" => [
                    "width" => $width,
                    "height" => $height
                ],
                "orientation" => $width > $height ? 'landscape' : 'portrait',
                "slug" => $image['slug'],
                "path" => $this->getImagePath($image),
                "href" => $this->getImageSrc($image),
            );
        };
    }

    public function thumbnail(Request $request)
    {
        $url = $request->input('href');
        $path = parse_url($url, PHP_URL_PATH);
        $path = public_path($path);
        abort_unless(File::exists($path) && File::isFile($path), 404);

        return $this->serveResizedImage($request, $path, 320, 320, 'thumbs');
    }

    /**
     * Generate (and persist) a resized WebP version of an image, served with
     * proper HTTP caching headers. The generated file is stored in
     * storage/app/public/{cacheDir}/{uuid}.webp so subsequent requests are
     * served straight from disk without invoking Imagick again.
     */
    private function serveResizedImage(Request $request, string $sourcePath, int $maxWidth, int $maxHeight, string $cacheDir)
    {
        $uuid = pathinfo($sourcePath, PATHINFO_FILENAME);
        $cachedPath = storage_path("app/public/{$cacheDir}/{$uuid}.webp");

        // Generate and persist the resized WebP if it doesn't exist yet
        if (!File::exists($cachedPath)) {
            File::ensureDirectoryExists(dirname($cachedPath));

            $imageManager = new ImageManager(new Driver());
            $dst = $imageManager->decode($sourcePath);
            $dst->core()->native()->stripImage();
            $dst->scale($maxWidth, $maxHeight);
            $webp = $dst->encode(new WebpEncoder(80));
            unset($imageManager, $dst);

            File::put($cachedPath, (string) $webp);
            $this->pruneThumbnailCache(dirname($cachedPath));
        }

        $lastModified = File::lastModified($cachedPath);
        $eTag = '"' . md5($cachedPath . $lastModified) . '"';

        // Check if client has a cached version via ETag
        if ($request->getETags() && in_array($eTag, $request->getETags())) {
            return response('', 304);
        }

        // Check if client has a cached version via If-Modified-Since
        if ($request->header('If-Modified-Since')) {
            $ifModifiedSince = strtotime($request->header('If-Modified-Since'));
            if ($lastModified <= $ifModifiedSince) {
                return response('', 304);
            }
        }

        return response(File::get($cachedPath), 200)
            ->header('Content-Type', 'image/webp')
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('ETag', $eTag)
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
    }

    /**
     * Remove the oldest files from the thumbnail cache directory when the
     * total size exceeds the configured limit (THUMB_CACHE_MAX_MB, default 5 MB).
     */
    private function pruneThumbnailCache(string $cacheDir): void
    {
        $maxBytes = (int) env('THUMB_CACHE_MAX_MB', 500) * 1024 * 1024;

        $files = collect(File::files($cacheDir))
            ->map(fn($f) => ['path' => $f->getPathname(), 'size' => $f->getSize(), 'mtime' => $f->getMTime()])
            ->sortBy('mtime'); // oldest first

        $totalSize = $files->sum('size');

        foreach ($files as $file) {
            if ($totalSize <= $maxBytes) {
                break;
            }
            File::delete($file['path']);
            $totalSize -= $file['size'];
        }
    }

    /**
     * @param array $gallery
     * @return string
     */
    private function getImageSrc(array|GalleryImage $gallery): string
    {
        $pathinfo = pathinfo($gallery['href']);
        $src = "{$gallery['file_id']}.{$pathinfo['extension']}";
        $src = env('APP_URL') . '/images/gallery/' . $gallery['slug'] . '/' . $src;
        return $src;
    }

    /**
     * @param array $gallery
     * @return string
     */
    private function getImagePath(array|GalleryImage $gallery): string
    {
        $pathinfo = pathinfo($gallery['href']);
        $src = "{$gallery['file_id']}.{$pathinfo['extension']}";
        $src = '/images/gallery/' . $gallery['slug'] . '/' . $src;
        return $src;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getGalleryImage(string $id): string
    {
// Allow $id to be <uuid>.jpg and strip file extension
        $filename = pathinfo(parse_url($id, PHP_URL_PATH), PATHINFO_FILENAME);
        $id = $filename;
        $image = GalleryImage::find($id);
        abort_unless($image != null, 404);

        $data = ($this->mapToImageResponse())($image);

        $href = $data['href'] ?? null;
        abort_unless(is_string($href) && $href !== '', 404);

        $path = $data['path'] ?? null;
        abort_unless(is_string($path) && $path !== '', 404);

        $path = public_path($path);
        abort_unless(File::exists($path) && File::isFile($path), 404);
        return $path;
    }
}
