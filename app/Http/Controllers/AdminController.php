<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\GalleryImageTag;
use App\Models\GalleryUser;
use App\Services\GallerySyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController
{
    private GallerySyncService $syncService;

    public function __construct(GallerySyncService $gallerySyncService)
    {
        $this->syncService = $gallerySyncService;
    }

    public function index()
    {
        $users = $this->get_all_users();

        return view('admin.index', compact('users'));
    }

    public function users()
    {
        $users = $this->get_all_users();

        return view('admin.users', compact('users'));
    }

    private function get_all_users()
    {
        return GalleryUser::orderBy('is_admin', 'DESC')->orderBy('token')->get();
    }

    public function create_user(Request $request)
    {
        if ($request->method() == 'POST') {
            $user = new GalleryUser;
            $attributes = request()->all();
            $user->fill([
                ...$attributes,
                'is_admin' => isset($attributes['is_admin']) ? 1 : 0,
                'token' => $attributes['user_token'],
            ]);
            $user->save();

            return redirect()->route('admin.user', ['user_id' => $user->user_id])
                ->with('success', __('messages.user_created'));
        }

        $users = $this->get_all_users();

        return view('admin.user_new', compact('users'));
    }

    public function user(Request $request, $user_id)
    {
        $users = $this->get_all_users();
        $user = $users->first(function ($user) use ($user_id) {
            return $user->user_id == $user_id;
        });

        $checkedTags = [];
        foreach ($user->tags->toArray() as $checkedTag) {
            $checkedTags[$checkedTag['scope'] ?: 'global'][] = $checkedTag;
        }

        $globalCheckedTagIds = array_map(
            fn ($tag) => $tag['tag_id'],
            $checkedTags['global'] ?? []
        );

        $galleryImageTags = GalleryImageTag::all()->sortBy('tag_value');

        $galleries = GalleryImage::select('slug', 'name')
            ->groupBy('slug', 'name')
            ->orderBy('slug', 'desc')
            ->get()
            ->toArray();

        $isCurrentUser = $user->user_id == $request->session()->get('user')->user_id;

        return view('admin.user', compact('user', 'users', 'galleries', 'galleryImageTags', 'checkedTags', 'globalCheckedTagIds', 'isCurrentUser'));
    }

    public function save_user($user_id): RedirectResponse
    {
        $user = GalleryUser::find($user_id);
        $attributes = request()->all();
        $user->fill([
            ...$attributes,
            'is_admin' => isset($attributes['is_admin']) ? 1 : 0,
            'token' => $attributes['user_token'],
        ]);
        $user->tags()->detach();

        if (isset($attributes['tag'])) {
            $newTags = [];
            foreach ($attributes['tag'] as $gallery_slug => $tags) {
                $mapped = array_map(function ($tag) use ($gallery_slug, $user) {
                    return [
                        'tag_id' => $tag,
                        'scope' => $gallery_slug,
                        'user_id' => $user->user_id,
                    ];
                }, $tags);

                $newTags = array_merge($newTags, $mapped);
            }
            $user->tags()->attach($newTags);
        }

        $user->save();

        return redirect()->route('admin.user', ['user_id' => $user_id])
            ->with('success', __('messages.user_saved'));
    }

    public function delete_user($user_id): RedirectResponse
    {
        $user = GalleryUser::find($user_id);
        $user->tags()->detach();
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', __('messages.user_deleted'));
    }

    public function galleries()
    {
        $users = $this->get_all_users();

        $galleries = GalleryImage::selectRaw('slug, name, COUNT(*) as image_count')
            ->groupBy('slug', 'name')
            ->orderBy('slug', 'desc')
            ->get();

        return view('admin.galleries', compact('users', 'galleries'));
    }

    public function clean(string $name): JsonResponse
    {
        try {
            $this->syncService->cleanGallery($name);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error cleaning gallery'], 500);
        }
    }

    public function sync(Request $request, ?string $name = null): JsonResponse
    {
        $options = [];
        if ($request->has('skip-download')) {
            $options['skip-download'] = filter_var($request->input('skip-download'), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('force')) {
            $options['force'] = filter_var($request->input('force'), FILTER_VALIDATE_BOOLEAN);
        }

        $this->syncService->setOptions($options);

        $syncResult = $this->syncService->syncGallery($name);

        if ($syncResult === null) {
            return response()->json(['error' => 'Error syncing galleries'], 500);
        }

        $mapDescriptor = function ($d) {
            return [
                'fileid' => $d->fileid,
                'href' => $d->href,
                'displayname' => $d->displayname,
                'isFolder' => $d->isFolder,
                'tags' => $d->tags,
                'gallery' => $d->gallery,
                'section' => $d->section,
            ];
        };

        $filesToDownload = array_map($mapDescriptor, $syncResult->filesToDownload);
        $filesToRemove = array_map($mapDescriptor, $syncResult->filesToRemove);
        $filesUntouched = array_map($mapDescriptor, $syncResult->filesUntouched);

        return response()->json([
            'counts' => [
                'toDownload' => count($filesToDownload),
                'toRemove' => count($filesToRemove),
                'untouched' => count($filesUntouched),
            ],
            'filesToDownload' => $filesToDownload,
            'filesToRemove' => $filesToRemove,
            'filesUntouched' => $filesUntouched,
        ]);
    }
}
