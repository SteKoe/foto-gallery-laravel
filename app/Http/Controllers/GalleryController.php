<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Services\GallerySyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

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
        $image = GalleryImage::find($id);

        $response = $this->mapToImageResponse()($image);

        $path = public_path($response['href']);
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
        return function ($image) : array {
            $pathinfo = pathinfo($image['href']);
            $src = "{$image['file_id']}.{$pathinfo['extension']}";

            [$width, $height] = storage_path(('app/public/gallery/' . $image['slug'] . '/' . $src));

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
                "href" => $this->getImageSrc($image)
            );
        };
    }

    /**
     * @param array $gallery
     * @return string
     */
    private function getImageSrc(array | GalleryImage $gallery): string
    {
        $pathinfo = pathinfo($gallery['href']);
        $src = "{$gallery['file_id']}.{$pathinfo['extension']}";
        $src = env('APP_URL') . '/images/gallery/' . $gallery['slug'] . '/' . $src;
        return $src;
    }
}
