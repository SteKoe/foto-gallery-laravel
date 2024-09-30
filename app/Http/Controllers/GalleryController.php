<?php
namespace App\Http\Controllers;

use App\Models\GalleryImage;

class GalleryController
{
    function index(string $slug)
    {
        $images = [
            '20240830-162509.jpg',
            '20240830-163452.jpg',
            '20240830-163525.jpg',
            '20240830-163715.jpg',
            '20240830-163921.jpg',
            '20240830-164728.jpg',
            '20240830-165321.jpg',
            '20240830-165415.jpg',
            '20240830-165523.jpg',
        ];

        return view('gallery', compact('slug', 'images'));
    }
}
