<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? 'Fotogalerie' }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite('resources/css/app.css')
</head>
<body>
<x-header :title="$title ?? ''" :subtitle="$subtitle  ?? ''" />
<main class="container">
    {{ $slot }}
</main>
<x-footer />
@vite('resources/js/main.ts')
</body>
</html>
