<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;

Route::get('/', function () {
    return view('galleries');
});

Route::get('/galleries/{slug}', [GalleryController::class, 'index']);
