<x-layout title="Galerien" subtitle="Alle Galerien">
    <div class="page-galleries">
        <div class="gallery-images">
            @forelse ($galleries as $idx => $gallery)
            <a
                href="{{ route('gallery', ['slug' => $gallery['slug']]) }}"
                class="gallery-image--polaroid"
            >
                <div class="gallery-title">
                    <h2>{{ $gallery['name_no_date'] }}</h2>
                </div>
                <img
                    src="{{ '/image/' . $gallery['cover'] }}"
                    alt="{{ $gallery['cover'] }}"
                >
            </a>
            @empty
            <p>No images</p>
            @endforelse
        </div>
    </div>
</x-layout>
