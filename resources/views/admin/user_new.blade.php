<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <h1 class="h1">
        {{ __('messages.user_new') }}
    </h1>

    <form class="form--admin" action="{{ url('/admin/user') }}" method="post">
        @csrf

        <div class="mb-5">
            <label for="token" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Token</label>
            <input type="text"
                   id="token"
                   name="user_token"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                   required/>
        </div>

        <div class="flex items-start mb-5">
            <div class="flex items-center h-5">
                <input id="is_admin" type="checkbox" value="" class="w-4 h-4
                border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700
                dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800"/>
            </div>
            <label for="is_admin"
                   class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Administrator</label>
        </div>


        <div class="btn-group">
            <x-form.button type="submit">
                {{ __('messages.save') }}
            </x-form.button>
        </div>
    </form>
</x-admin-layout>
