<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">Settings</h1>

    <h2 class="h2">Token Settings</h2>
    <form class="form--admin" action="{{ url('/admin/user/' . $user->user_id) }}" method="post">
        @csrf

        <label class="form-group">
            <span class="label">Token:</span>
            <input class="input" type="text" name="token" value="{{ $user->token }}">
        </label>

        <label class="form-group">
            <span class="label">Is Admin:</span>
            <input class="input" type="checkbox" id="is_admin" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }}>
        </label>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>

    @if($user->is_admin)
    <x-alert.info title="Hint">
        This user is an admin and has access to all images anyway.
    </x-alert.info>

    @else

    <h2 class="h2">Gallery Settings</h2>
    <p class="mb-8">
        On this page you can manage the gallery settings for users.
        Access to images is granted based on the tags assigned to the images.
        Global tag settings are applied to all galleries.
        Tag settings for a specific gallery extend the global settings.
    </p>
    <x-admin.gallery-form
        :user="$user"
        :gallery="null"
        :galleryImageTags="$galleryImageTags"
        :checkedTags="array_key_exists('global', $checkedTags) ? $checkedTags['global'] : []"
    />

    @foreach ($galleries as $gallery)
        <x-admin.gallery-form
            :user="$user"
            :gallery="$gallery"
            :galleryImageTags="$galleryImageTags"
            :checkedTags="array_key_exists($gallery['slug'], $checkedTags) ? $checkedTags[$gallery['slug']] : []"
        />
    @endforeach

    @endif
</x-admin-layout>
