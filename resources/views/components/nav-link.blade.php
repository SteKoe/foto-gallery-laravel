<a class="{{ Request::routeIs($routeName) ? 'active' : '' }}" href="{{ route($routeName) }}" title="{{ $title }}">
    {{ $slot }}
</a>
