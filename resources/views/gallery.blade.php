<x-layout :subtitle="$name">
    <div class="page-gallery">
        <div class="gallery-images">
            @forelse ($images as $idx => $image)
            <a
                href="{{ "/". $image['href'] }}"
                class="gallery-image--polaroid"
            >
                <img
                    loading="lazy"
                    src="{{ "/". $image['href'] }}"
                    title="{{ $image['tags'] }}"
                >
            </a>
            @empty
            <p>No images</p>
            @endforelse
        </div>
    </div>
</x-layout>
