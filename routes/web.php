<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\CacheThumbnails;
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
    ->name('image')
    ->middleware(GalleryAuth::class);

Route::get('/thumb', [GalleryController::class, 'thumbnail'])
    ->middleware([GalleryAuth::class, CacheThumbnails::class]);

Route::get('/logout', [GalleryController::class, 'logout'])
    ->name('logout')
    ->middleware(GalleryAuth::class);

Route::get('/admin', [AdminController::class, 'index'])
    ->name('admin')
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);

Route::get('/admin/user', [AdminController::class, 'create_user'])
    ->name('admin.user.new')
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);
Route::post('/admin/user', [AdminController::class, 'create_user'])
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);

Route::get('/admin/user/{user_id}', [AdminController::class, 'user'])
    ->name('admin.user')
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);
Route::post('/admin/user/{user_id}', [AdminController::class, 'save_user'])
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);
Route::post('/admin/user/{user_id}/delete', [AdminController::class, 'delete_user'])
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);
Route::get('/admin/sync', [AdminController::class, 'sync'])
    ->name('admin.sync')
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);
Route::get('/admin/sync/{name}', [AdminController::class, 'sync'])
    ->name('admin.sync')
    ->middleware([
        GalleryAuth::class,
        AdminAuth::class
    ]);

