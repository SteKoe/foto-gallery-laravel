<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">
        {{ __('messages.settings_title') }}
        @if ($isCurrentUser)
        ({{ __('messages.settings_title_this_is_you') }})
        @endif
    </h1>

    @if($user->is_admin)
    <x-alert.info>
        {{ __('messages.settings_admin_hint') }}
    </x-alert.info>
    @endif

    <h2 class="h2">{{ __('messages.settings_token_title') }}</h2>
    <form class="form--admin" action="{{ url('/admin/user/' . $user->user_id) }}" method="post">
        @csrf

        <div class="mb-5">
            <label for="user_token" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Token</label>
            <input name="user_token" type="text"
                   @if($isCurrentUser) disabled @endif
                   id="user_token"
                   value="{{ $user->token }}"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   required/>
        </div>

        <div class="flex items-start mb-5">
            <div class="flex items-center h-5">
                <input id="is_admin"
                       type="checkbox"
                       @if($isCurrentUser) disabled @endif
                       class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800"
                       {{ $user->is_admin ? 'checked' : '' }}
                />
            </div>
            <label for="is_admin"
                   class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Administrator</label>
        </div>


        @if(!$user->is_admin)
        <h2 class="h2">{{ __('messages.settings_gallery_settings_title') }}</h2>
        <p class="mb-8">
            {{ __('messages.settings_gallery_settings_description') }}
        </p>

        <div class="mb-8">
            <x-admin.gallery-form
                :user="$user"
                :gallery="null"
                :galleryImageTags="$galleryImageTags"
                :checkedTags="array_key_exists('global', $checkedTags) ? $checkedTags['global'] : []"
            />
        </div>

        @foreach ($galleries as $gallery)
        <div class="mb-8">
            <x-admin.gallery-form
                :user="$user"
                :gallery="$gallery"
                :galleryImageTags="$galleryImageTags"
                :checkedTags="array_key_exists($gallery['slug'], $checkedTags) ? $checkedTags[$gallery['slug']] : []"
            />
        </div>
        @endforeach

        @endif

        <div class="btn-group">
            <x-form.button type="submit" :disabled="$isCurrentUser">
                {{ __('messages.save') }}
            </x-form.button>
        </div>
    </form>
</x-admin-layout>
