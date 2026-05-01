<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SIPAKAR') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#003366">
    <!-- iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIPAKAR">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bps: {
                            blue: '#003366',
                            amber: '#FFC107',
                        },
                        minimal: {
                            indigo: '#6366f1',
                            violet: '#7c3aed',
                            bg: '#f8fafc',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="font-sans antialiased bg-[#f8fafc] text-slate-900">
    <div class="min-h-screen flex">
        @auth
            @if(!in_array(session('active_role'), ['Ketua Tim', 'Pimpinan', 'Pegawai', 'Super Admin', 'Kepala Kabkot']))
                <livewire:layout.sidebar />
            @endif
            <div class="flex-1 flex flex-col min-h-screen {{ !in_array(session('active_role'), ['Ketua Tim', 'Pimpinan', 'Pegawai', 'Super Admin', 'Kepala Kabkot']) ? 'lg:pl-64' : '' }} w-full">
                <livewire:layout.navbar />
                <main class="p-6 lg:p-10 flex-1 w-full max-w-none">
                    {{ $slot }}
                </main>
                <footer class="p-6 text-center text-xs text-slate-500 border-t border-slate-200/60 bg-white">
                    &copy; 2026 SIPAKAR - Badan Pusat Statistik
                </footer>
            </div>
        @else
            <main class="w-full flex-1">
                {{ $slot }}
            </main>
        @endauth
    </div>

    @auth
        <livewire:auth.change-password />
    @endauth
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
