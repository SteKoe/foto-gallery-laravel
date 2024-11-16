@vite('resources/css/admin.css')

<x-layout title="Admin" subtitle="Administration">
    <section class="section--admin">
        <aside class="navigation--admin">
            <ul>
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.user.new') }}" class="bg">
                        <div class="flex justify-center items-center gap-1">
                            <x-icons.baseline-add-box />
                            {{ __('messages.user_new') }}
                        </div>
                    </a>
                </li>
                @foreach ($users as $user)
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.user', $user['user_id']) }}">
                        @if($user['is_admin'])
                            <x-icons.baseline-verified-user />
                        @endif
                        {{ $user['token'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </aside>
        <main class="content--admin">
            {{ $slot }}
        </main>
    </section>
</x-layout>
