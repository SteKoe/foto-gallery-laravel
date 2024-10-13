<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? 'asd' }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
<x-header :title="$title ?? ''" :subtitle="$subtitle  ?? ''" />
<main class="container">
    {{ $slot }}
</main>
<x-footer />
</body>
</html>
