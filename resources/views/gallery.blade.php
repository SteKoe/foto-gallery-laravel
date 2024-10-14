@vite('resources/css/app.css')

<x-layout :subtitle="$name">
    <div class="page-gallery">
        <div class="gallery-images">
            @forelse ($images as $idx => $image)
            <a
                href="{{ '/image/' . $image['file_id'] }}"
                class="gallery-image--polaroid"
            >
                <img
                    src="{{ '/image/' . $image['file_id'] }}"
                    alt="{{ $image['src'] }}"
                    title="{{ $image['tags'] }}"
                >
            </a>
            @empty
            <p>No images</p>
            @endforelse
        </div>
    </div>
</x-layout>
