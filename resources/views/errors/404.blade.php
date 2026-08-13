<x-layout title="404" subtitle="Seite nicht gefunden" :showBackButton="true">
    <div class="page-error flex flex-col items-center justify-center text-center py-16 gap-6">
        <img src="/images/404.png" alt="404" class="max-w-md md:max-w-lg w-full h-auto">
        <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 max-w-md">
            {{ __('messages.error_404_text', ['slug' => $slug ?? '']) }}
        </p>
        <a href="{{ route('home') }}" class="inline-block px-6 py-3 rounded-xl bg-black text-white dark:bg-white dark:text-black hover:opacity-80 transition">
            {{ __('messages.error_404_back') }}
        </a>
    </div>
</x-layout>
