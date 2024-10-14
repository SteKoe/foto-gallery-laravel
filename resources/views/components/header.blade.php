@vite('resources/css/header.css')

<nav class="navigation">
    <div class="container">
        <div class="navigation__content">
            <div>
                <h1>
                    <a href="{{ route('home') }}">
                        {{ config('app.name') }}
                    </a>
                </h1>
                <h2>
                    {{ $subtitle ?? '' }}
                </h2>
            </div>

            <ul class="navigation__links">
                @if (session()->has('token'))
                <li>
                    <x-nav-link route-name="logout">
                        Abmelden <small>({{ session()->get('token') }})</small>
                    </x-nav-link>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
