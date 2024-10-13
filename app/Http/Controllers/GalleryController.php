<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Services\GallerySyncService;
use Illuminate\Http\Request;

class GalleryController
{
    private GallerySyncService $gallerySyncService;

    public function __construct(GallerySyncService $gallerySyncService)
    {
        $this->gallerySyncService = $gallerySyncService;
    }

    function index()
    {
        $tags = [9];
        $galleries = $this->getScopedGalleryImageQuery()->groupBy('slug')->get()->toArray();

        $galleries = array_map(function ($gallery) {
            $pathinfo = pathinfo($gallery['href']);
            $gallery['cover'] = $gallery['file_id'];
            $gallery['name_no_date'] = preg_replace('/\d{4}(\.\d{2}){0,2}/', '', $gallery['name']);
            return $gallery;
        }, $galleries);

        krsort($galleries);

        return view('galleries', compact('galleries'));
    }

    function gallery(string $slug)
    {
        $images = $this->getScopedGalleryImageQuery()->where('slug', $slug)->get();

        $name = $images[0]['name'];
        $images = $images->map($this->mapToImageResponse(), $images);

        return view('gallery', compact('slug', 'images', 'name'));
    }

    function image(string $id, Request $request)
    {
        $image = GalleryImage::find($id);

        ob_start();
        $this->gallerySyncService->downloadFile($image, $image['file_id']);
        ob_clean();

        $response = $this->mapToImageResponse()($image);

        $path = public_path($response['href']);
        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)->header('Content-Type', $type);
    }

    private function getScopedGalleryImageQuery()
    {
        $tags = [9];
        return GalleryImage::whereIn('file_id', function ($subquery) use ($tags) {
            $subquery->select('git.file_id')
                ->from('gallery_image_gallery_image_tag as git')
                ->whereIn('git.tag_id', $tags)
                ->whereNotIn('git.file_id', function ($subquery2) use ($tags) {
                    $subquery2->select('git.file_id')
                        ->from('gallery_image_gallery_image_tag as git')
                        ->whereNotIn('git.tag_id', $tags);
                })
                ->groupBy('git.file_id');
        });
    }

    /**
     * @return \Closure
     */
    public function mapToImageResponse(): \Closure
    {
        return function ($image) {
            $pathinfo = pathinfo($image['href']);
            $src = "{$image['file_id']}.{$pathinfo['extension']}";

            return array(
                "file_id" => $image['file_id'],
                "tags" => join(',', array_map(function ($image) {
                    return $image['tag_value'];
                }, $image->tags()->get()->toArray())),
                "src" => $src,
                "slug" => $image['slug'],
                "href" => 'images/gallery/' . $image['slug'] . '/' . $src,
            );
        };
    }
}
