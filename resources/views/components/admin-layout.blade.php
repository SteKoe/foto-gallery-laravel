@vite('resources/css/admin.css')

<x-layout title="Admin" subtitle="Administration">
    <section class="section--admin">
        <aside class="navigation--admin">
            <ul>
                @foreach ($users as $user)
                <li class="navigation--admin__link">
                    <a href="{{ route('admin.user', $user['user_id']) }}">
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
