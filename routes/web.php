<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GalleryController;

Route::get('/', [GalleryController::class, 'index'])->name('home');
Route::get('/impressum', [PageController::class, 'impressum'])->name('impressum');
Route::get('/gallery/{slug}', [GalleryController::class, 'gallery'])->name('gallery');
Route::get('/image/{id}', [GalleryController::class, 'image'])->name('image');
