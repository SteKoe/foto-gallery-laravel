<x-layout title="Galerien" subtitle="Alle Galerien">
    <div class="page-galleries">
        <div class="gallery-images">
            @forelse ($galleries as $idx => $gallery)
            <a
                href="{{ route('gallery', ['slug' => $gallery['slug']]) }}"
                class="gallery-image"
            >
                <div class="gallery-title">
                    <h2>{{ $gallery['name_no_date'] }}</h2>
                </div>
                <div class="image-list">
                    <div class="image-list--item">
                        <img
                            loading="lazy"
                            src="{{ $gallery['cover'] }}"
                            alt="{{ $gallery['cover'] }}"
                        >
                    </div>
                    @foreach($gallery['more_images'] as $idx => $more_image)
                    <div class="image-list--item">
                        <img
                            loading="lazy"
                            src="{{ $more_image }}"
                        >
                        @if($gallery['total_images'] > 0 && $idx === count($gallery['more_images'])-1)
                        <div class="label">
                            +{{ $gallery['total_images'] }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </a>
            @empty
            <p>No images</p>
            @endforelse
        </div>
    </div>
</x-layout>
