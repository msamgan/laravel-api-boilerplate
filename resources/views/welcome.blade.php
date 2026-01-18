<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
</head>

<body
    class="antialiased bg-gradient-to-b from-[#FDFDFC] to-white dark:from-[#030303] dark:to-[#0a0a0a] min-h-screen flex items-center justify-center">
<div class="w-full max-w-3xl px-6">
    <div
        class="relative flex flex-col md:flex-row items-center bg-white dark:bg-[#111111] rounded-2xl shadow-xl overflow-hidden">
        <div class="w-full md:w-1/2 p-10 text-center">
            <h1 class="text-3xl sm:text-4xl font-semibold leading-tight text-[#1b1b18] dark:text-[#EDEDEC]">{{ config('app.name', 'Laravel') }}</h1>
        </div>
    </div>
</div>
</body>
</html>
