<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100">
            <a href="/" wire:navigate class="mb-6">
                <x-application-logo class="text-2xl" />
            </a>

            <div class="w-[28rem] rounded-lg bg-white px-8 py-8 shadow-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
