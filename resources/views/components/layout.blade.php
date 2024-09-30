<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>{{ $title ?? 'Example Website' }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite('resources/css/app.css')
</head>
<body>
<nav class="main-navigation">
    <h1>Fotos</h1>
    <div>
        asd
    </div>
</nav>
<main>
    {{ $slot }}
</main>
</body>
</html>
