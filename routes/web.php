<?php

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\GalleryAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', [GalleryController::class, 'index'])
    ->name('home')
    ->middleware(GalleryAuth::class);

Route::get('/impressum', [PageController::class, 'impressum'])
    ->name('impressum');

Route::get('/gallery/{slug}', [GalleryController::class, 'gallery'])
    ->name('gallery')
    ->middleware(GalleryAuth::class);

Route::get('/image/{id}', [GalleryController::class, 'image'])
    ->name('image');

Route::get('/logout', [GalleryController::class, 'logout'])
    ->name('logout');
