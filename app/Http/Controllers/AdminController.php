<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GalleryUser;
use Illuminate\Http\RedirectResponse;

class AdminController
{
    public function index()
    {
        $users = GalleryUser::all()->toArray();

        return view('admin.index', compact('users'));
    }

    public function user($user_id)
    {
        $users = GalleryUser::all();
        $user = $users->first(function ($user) use ($user_id) {
            return $user->user_id == $user_id;
        });

        $checkedTags = [];
        foreach ($user->tags->toArray() as $checkedTag) {
            $checkedTags[$checkedTag['scope'] ?: "global"][] = $checkedTag;
        }

        $galleryImageTags = GalleryImageTag::all()->sortBy('tag_value');

        $galleries = GalleryImage::groupBy('slug')
            ->orderBy('slug', 'desc')
            ->get()
            ->toArray();

        return view('admin.gallery', compact('user', 'users', 'galleries', 'galleryImageTags', 'checkedTags'));
    }

    public function save_user($user_id): RedirectResponse
    {
        $user = GalleryUser::find($user_id);
        $user->update(request()->all());
        $user->save();

        return redirect()->route('admin.user', ['user_id' => $user_id]);
    }

    public function save_user_permissions($user_id): RedirectResponse
    {
        $user = GalleryUser::find($user_id);

        $arr = request('tag') ?: $user->tags();
        $gallery_slug = request('gallery_slug') ?: null;

        $tags_to_remove = array_map(function ($tag) {
            return $tag['tag_id'];
        }, array_filter($user->tags->toArray(), function ($tag) use ($gallery_slug) {
            return $tag['scope'] == $gallery_slug;
        }));

        $user->tags()->detach($tags_to_remove);

        $user->tags()->attach(array_map(function ($tag) use ($gallery_slug, $user) {
            return [
                'tag_id' => $tag,
                'scope' => $gallery_slug,
                'user_id' => $user->user_id
            ];
        }, $arr));

        return redirect()->route('admin.user', ['user_id' => $user_id]);
    }
}
