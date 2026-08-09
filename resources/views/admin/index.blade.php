<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">{{ __('messages.admin') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('admin.galleries') }}"
           class="block p-6 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
            <div class="flex items-center gap-3 mb-2">
                <x-icons.baseline-photo-library />
                <h2 class="h2 !pb-0">{{ __('messages.galleries') }}</h2>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.synced_galleries') }}</p>
        </a>

        <a href="{{ route('admin.users') }}"
           class="block p-6 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
            <div class="flex items-center gap-3 mb-2">
                <x-icons.baseline-verified-user />
                <h2 class="h2 !pb-0">{{ __('messages.users') }}</h2>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.users_description') }}</p>
        </a>
    </div>
</x-admin-layout>
