<x-admin-layout title="Admin" subtitle="Administration" :users="$users">
    <pre><code>{{ json_encode($galleries, JSON_PRETTY_PRINT) }}</code></pre>
</x-admin-layout>
