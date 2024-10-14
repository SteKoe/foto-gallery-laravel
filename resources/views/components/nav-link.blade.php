<a class="{{ Request::routeIs($routeName) ? 'active' : '' }}"  href="{{ route($routeName) }}">
    {{ $slot }}
</a>
