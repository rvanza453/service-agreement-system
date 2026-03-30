<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <style>
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-blob {
                animation: blob 7s infinite;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
        
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 relative overflow-hidden">
            <!-- Animated Background Shapes -->
            <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>

            <div class="relative z-10 w-full sm:max-w-md mt-6 px-8 pt-8 bg-white/90 backdrop-blur-xl shadow-2xl sm:rounded-[0.5rem] border border-white/20 ring-1 ring-black/5" style="padding-bottom: 4rem; border-radius: 2.5rem;">
            <div class="flex flex-col items-center mb-8">
                @php
                    $path = public_path('images/saraswantiLogo.png');
                    $logoData = null;

                    if (file_exists($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $logoData = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp

                @if($logoData)
                    <img src="{{ $logoData }}" alt="Saraswanti Logo" class="h-20 w-auto drop-shadow-xl">
                @else
                    <div class="text-gray-800 font-bold text-2xl">SARASWANTI</div>
                @endif
            </div>
                {{ $slot }}
            </div>
            
            <div class="mt-8 text-white/40 text-sm z-10">
                &copy; {{ date('Y') }} <a href="https://github.com/rvanza453" target="_blank" class="hover:text-white transition-colors">revanza</a>. All rights reserved.
            </div>
        </div>
    </body>
</html>
