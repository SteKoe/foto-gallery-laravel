@vite('resources/css/header.css')

<nav class="navigation">
    <div class="container">
        <div class="navigation__content">
            @if ($attributes['showBackButton'] ?? false)
            <div class="navigation__back">
                <a href="{{ route('home') }}">
                    <x-icons.back_button />
                </a>
            </div>
            @endif

            <div class="page-title">
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
                @if (session()->has('user') && session()->get('user')->is_admin == true)
                <li>
                    <x-nav-link route-name="admin">
                        Admin
                    </x-nav-link>
                </li>
                @endif

                @if (session()->has('token'))
                <li>
                    <x-nav-link route-name="logout" :title="__('messages.sign_out')">
                        <div class="hidden md:inline">
                            {{ __('messages.sign_out') }} <small class="hidden sm:inline text-gray-400 italic">({{ session()->get('token') }})</small>
                        </div>
                        <div class="inline md:hidden">
                            <x-icons.logout />
                        </div>
                    </x-nav-link>
                </li>
                @endif

            </ul>
        </div>
    </div>
</nav>
