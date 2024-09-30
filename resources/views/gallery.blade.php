@vite('resources/css/gallery-image.css')

<x-layout>
    <x-slot name="title">
        SteKoes Foto Gallery | Gallery {{ $slug }}
    </x-slot>

    <div>
        <div class="gallery-images">
            @forelse ($images as $image)
                <img
                    class="gallery-image--polaroid gallery-image--polaroid__30"
                    src="{{ asset('/images/gallery/' . $slug . '/thumb_' . $image) }}"
                    alt="{{ $image }}"
                >
            @empty
                <p>No images</p>
            @endforelse
        </div>
    </div>
</x-layout>
