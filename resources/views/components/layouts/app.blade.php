<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">



    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <style>
        body {
            width: 100%;
            height: 100%;
            background: url('{{ asset('images/barangaybackground1.jpg') }}') center no-repeat;
            background-size: cover;
            background-attachment: fixed;
            top: 0;
            left: 0;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    {{ $slot }}

    @livewire('notifications')
    {{-- @livewire('terms-modal') --}}
    {{-- @include('components.terms-modal') --}}
    
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @filamentScripts
    @vite('resources/js/app.js')

    @livewire('livewire-ui-modal')

</body>

</html>
