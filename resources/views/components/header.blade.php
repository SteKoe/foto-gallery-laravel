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

                <div class="flex flex-col justify-center ml-3">
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="light-switch" class="light-switch hidden" />
                        <svg class="dark:hidden" width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor" d="M7 0h2v2H7zM12.88 1.637l1.414 1.415-1.415 1.413-1.413-1.414zM14 7h2v2h-2zM12.95 14.433l-1.414-1.413 1.413-1.415 1.415 1.414zM7 14h2v2H7zM2.98 14.364l-1.413-1.415 1.414-1.414 1.414 1.415zM0 7h2v2H0zM3.05 1.706 4.463 3.12 3.05 4.535 1.636 3.12z" />
                            <path fill="currentColor" d="M8 4C5.8 4 4 5.8 4 8s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4Z" />
                        </svg>
                        <svg class="hidden dark:block" width="16" height="16" xmlns="http://www.w3.org/2000/svg">
                            <path fill="currentColor" d="M6.2 1C3.2 1.8 1 4.6 1 7.9 1 11.8 4.2 15 8.1 15c3.3 0 6-2.2 6.9-5.2C9.7 11.2 4.8 6.3 6.2 1Z" />
                            <path fill="currentColor" d="M12.5 5a.625.625 0 0 1-.625-.625 1.252 1.252 0 0 0-1.25-1.25.625.625 0 1 1 0-1.25 1.252 1.252 0 0 0 1.25-1.25.625.625 0 1 1 1.25 0c.001.69.56 1.249 1.25 1.25a.625.625 0 1 1 0 1.25c-.69.001-1.249.56-1.25 1.25A.625.625 0 0 1 12.5 5Z" />
                        </svg>
                        <span class="sr-only">Switch to light / dark version</span>
                    </label>
                </div>

                @if (session()->has('user') && session()->get('user')->is_admin == true)
                <li>
                    <x-nav-link route-name="admin">
                        <div class="hidden md:inline">
                            {{ __('messages.admin') }}
                        </div>
                        <div class="inline md:hidden text-2xl">
                            <x-icons.admin />
                        </div>
                    </x-nav-link>
                </li>
                @endif

                @if (session()->has('token'))
                <li>
                    <x-nav-link route-name="logout" :title="__('messages.sign_out')">
                        <div class="hidden md:inline">
                            {{ __('messages.sign_out') }} <small class="hidden sm:inline text-gray-400 italic">({{ session()->get('token') }})</small>
                        </div>
                        <div class="inline md:hidden text-2xl">
                            <x-icons.logout />
                        </div>
                    </x-nav-link>
                </li>
                @endif

            </ul>
        </div>
    </div>
</nav>
