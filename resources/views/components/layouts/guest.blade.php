<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <x-pwa-head />
    <title>{{ $title ?? 'EclesTres' }}</title>
    @vite(['resources/css/app.css'])
</head>
    <body class="min-h-screen flex flex-col">
        <div class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <x-brand size="text-3xl" />
                </div>

                <div class="bg-mate-superficie border border-mate-borde rounded-lg p-6 sm:p-8 shadow-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <x-footer />
        @stack('scripts')
    </body>
</html>