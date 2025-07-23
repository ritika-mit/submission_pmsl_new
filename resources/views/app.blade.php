<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title inertia>{{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" media="print" onload="this.media='all'" />
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" />
    </noscript>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    @vite('resources/ts/app.ts')
    @inertiaHead
    @stack('head')
</head>

<body class="bg-primary-50 bg-opacity-5">
    @inertia
</body>

</html>