@vite('resources/views/components/alert/alert.css')

<div class="alert alert--error">
    @if(isset($title))
        <h4 class="alert__title">{{ $title }}</h4>
    @endif
    {{ $slot }}
</div>
