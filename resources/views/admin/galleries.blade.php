<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">{{ __('messages.synced_galleries') }}</h1>

    @if ($galleries->isEmpty())
        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_synced_galleries') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-black/5 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3">{{ __('messages.gallery_name') }}</th>
                        <th class="px-4 py-3">{{ __('messages.gallery_slug') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('messages.gallery_image_count') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('messages.gallery_action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($galleries as $gallery)
                    <tr class="border-b border-black/5 dark:border-white/5">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('gallery', ['slug' => $gallery->slug]) }}" class="hover:underline">
                                {{ $gallery->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400">
                            {{ $gallery->slug }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ $gallery->image_count }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-2">
                                <button
                                    type="button"
                                    class="resync-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-600 dark:hover:bg-blue-700"
                                    data-sync-url="{{ url('/admin/sync/' . rawurlencode($gallery->name)) }}"
                                    data-label="{{ __('messages.resync') }}"
                                    data-label-loading="{{ __('messages.resyncing') }}"
                                    data-msg-success="{{ __('messages.resync_success', ['count' => ':count', 'removed' => ':removed']) }}"
                                    data-msg-error="{{ __('messages.resync_error') }}"
                                >
                                    <x-icons.baseline-refresh />
                                    <span class="resync-btn__label">{{ __('messages.resync') }}</span>
                                </button>
                                <button
                                    type="button"
                                    class="delete-btn inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-600 dark:hover:bg-red-700"
                                    data-clean-url="{{ route('admin.clean', ['name' => $gallery->name]) }}"
                                    data-gallery-name="{{ $gallery->name }}"
                                    data-msg-confirm="{{ __('messages.delete_confirm', ['name' => ':name']) }}"
                                    data-msg-success="{{ __('messages.delete_success') }}"
                                    data-msg-error="{{ __('messages.delete_error') }}"
                                >
                                    <x-icons.delete />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        document.querySelectorAll('.resync-btn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const label = btn.querySelector('.resync-btn__label');
                const url = btn.dataset.syncUrl;
                const okMsg = btn.dataset.msgSuccess;
                const errMsg = btn.dataset.msgError;

                btn.disabled = true;
                label.textContent = btn.dataset.labelLoading;

                let msgEl = btn.nextElementSibling;
                if (!msgEl || !msgEl.classList.contains('resync-msg')) {
                    msgEl = document.createElement('span');
                    msgEl.className = 'resync-msg block mt-1 text-xs';
                    btn.parentNode.appendChild(msgEl);
                }

                try {
                    const res = await fetch(url);
                    if (!res.ok) throw new Error(res.status);

                    const data = await res.json();
                    const downloaded = data.counts?.toDownload ?? 0;
                    const removed = data.counts?.toRemove ?? 0;

                    msgEl.textContent = okMsg
                        .replace(':count', downloaded)
                        .replace(':removed', removed);
                    msgEl.className = 'resync-msg block mt-1 text-xs text-green-600 dark:text-green-400';
                } catch (e) {
                    msgEl.textContent = errMsg;
                    msgEl.className = 'resync-msg block mt-1 text-xs text-red-600 dark:text-red-400';
                } finally {
                    btn.disabled = false;
                    label.textContent = btn.dataset.label;
                }
            });
        });

        document.querySelectorAll('.delete-btn').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const url = btn.dataset.cleanUrl;
                const galleryName = btn.dataset.galleryName;
                const confirmMsg = btn.dataset.msgConfirm.replace(':name', galleryName);
                const okMsg = btn.dataset.msgSuccess;
                const errMsg = btn.dataset.msgError;

                if (!confirm(confirmMsg)) return;

                btn.disabled = true;

                let msgEl = btn.parentNode.nextElementSibling;
                if (!msgEl || !msgEl.classList.contains('delete-msg')) {
                    msgEl = document.createElement('span');
                    msgEl.className = 'delete-msg block mt-1 text-xs';
                    btn.parentNode.parentNode.appendChild(msgEl);
                }

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    if (!res.ok) throw new Error(res.status);

                    msgEl.textContent = okMsg;
                    msgEl.className = 'delete-msg block mt-1 text-xs text-green-600 dark:text-green-400';

                    setTimeout(() => location.reload(), 1000);
                } catch (e) {
                    msgEl.textContent = errMsg;
                    msgEl.className = 'delete-msg block mt-1 text-xs text-red-600 dark:text-red-400';
                } finally {
                    btn.disabled = false;
                }
            });
        });
    </script>
</x-admin-layout>
