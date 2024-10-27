<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <pre><code>{{ json_encode($users, JSON_PRETTY_PRINT) }}</code></pre>
</x-admin-layout>
