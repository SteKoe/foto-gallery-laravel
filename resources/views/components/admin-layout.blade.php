@vite('resources/css/admin.css')

<x-layout title="Admin" subtitle="Administration">
    <section class="section--admin">
        <aside class="navigation--admin">
            <ul>
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.galleries') }}" class="bg">
                        <div class="flex justify-center items-center gap-1">
                            <x-icons.baseline-photo-library />
                            {{ __('messages.galleries') }}
                        </div>
                    </a>
                </li>
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.users') }}" class="bg">
                        <div class="flex justify-center items-center gap-1">
                            <x-icons.baseline-verified-user />
                            {{ __('messages.users') }}
                        </div>
                    </a>
                </li>
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.user.new') }}" class="bg">
                        <div class="flex justify-center items-center gap-1">
                            <x-icons.baseline-add-box />
                            {{ __('messages.user_new') }}
                        </div>
                    </a>
                </li>
            </ul>
        </aside>
        <main class="content--admin">
            {{ $slot }}
        </main>
    </section>
</x-layout>
