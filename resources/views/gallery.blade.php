@vite('resources/css/app.css')

<x-layout :subtitle="$name">
    <div class="page-gallery gallery-images">
        @forelse ($images as $idx => $image)
        <img
            class="gallery-image--polaroid {{ $idx % 3 === 0 ? 'gallery-image--sm' : '' }}"
            src="{{ '/image/' . $image['file_id'] }}"
            alt="{{ $image['src'] }}"
            title="{{ $image['tags'] }}"
        >
        @empty
        <p>No images</p>
        @endforelse
    </div>
</x-layout>
