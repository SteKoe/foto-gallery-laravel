<x-layout :subtitle="$name">
    <div class="page-gallery">
        <div class="gallery-images">
            @forelse ($images as $idx => $image)
            <a
                href="{{ "/". $image['href'] }}"
                class="gallery-image"
            >
                <img
                    loading="lazy"
                    data-orientation="{{ $image['orientation'] }}"
                    src="{{ "/". $image['href'] }}"
                    title="{{ $image['tags'] }}"
                >
            </a>
            @empty
            <p>No images</p>
            @endforelse
        </div>
    </div>

    @vite('resources/js/lightgallery.ts')
</x-layout>
