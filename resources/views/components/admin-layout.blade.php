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
                        @else
                            <x-icons.token-key />
                        @endif
                        <div class="flex-1">
                            {{ $user['token'] }}
                        </div>

                        <form method="POST" action="/admin/user/{{ $user['user_id'] }}/delete" class="justify-end">
                            @csrf
                            <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-2 py-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                                <x-icons.delete />
                            </button>
                        </form>
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
