<x-layout :subtitle="$name" :showBackButton="true">
    <div class="page-gallery">
        <div class="gallery-images">
            @forelse ($images as $idx => $image)
            <a
                href="{{ $image['href'] }}"
                class="gallery-image"
            >
                <img
                    loading="lazy"
                    alt=""
                    data-orientation="{{ $image['orientation'] }}"
                    src="/thumb?href={{ urlencode($image['href']) }}"
                    title="{{ $image['tags'] }}"
                >
            </a>
            @empty
            <p class="text-center col-span-4">{{ __('messages.no_images') }}</p>
            @endforelse
        </div>
    </div>
</x-layout>

@vite('resources/js/lightgallery.ts')
