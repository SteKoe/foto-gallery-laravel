<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">{{ __('messages.users') }}</h1>

    @if (session('success'))
        <x-alert.success>{{ session('success') }}</x-alert.success>
    @endif
    @if (session('error'))
        <x-alert.error>{{ session('error') }}</x-alert.error>
    @endif

    @if ($users->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_users') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-black/5 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3">{{ __('messages.user_token') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('messages.user_admin') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('messages.gallery_action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    @php
                        $deleteConfirm = __('messages.delete_confirm', ['name' => $user->token]);
                    @endphp
                    <tr class="border-b border-black/5 dark:border-white/5">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('admin.user', $user->user_id) }}" class="hover:underline">
                                {{ $user->token }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($user->is_admin)
                                <x-icons.baseline-verified-user />
                            @else
                                <x-icons.token-key />
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.user', $user->user_id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors dark:bg-blue-600 dark:hover:bg-blue-700">
                                    {{ __('messages.edit') }}
                                </a>
                                <form method="POST" action="/admin/user/{{ $user->user_id }}/delete" class="inline"
                                      onsubmit="return confirm('{{ $deleteConfirm }}')">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium transition-colors dark:bg-red-600 dark:hover:bg-red-700">
                                        <x-icons.delete />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
