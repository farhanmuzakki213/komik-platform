<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Webtoon Platform</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.store('theme', {
                theme: 'light',
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                init() {
                    // Penanganan resize diletakkan di dalam store, bukan di x-init body
                    window.addEventListener('resize', () => {
                        if (window.innerWidth < 1280) {
                            this.setMobileOpen(false);
                            this.isExpanded = false;
                        } else {
                            this.isMobileOpen = false;
                            this.isExpanded = true;
                        }
                    });
                },
                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },
                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },
                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },
                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            if (savedTheme === 'dark' || (!savedTheme && systemTheme === 'dark')) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>

<body x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 500)" class="dark:bg-gray-900 transition-colors duration-300">

    <div x-show="!loaded" class="fixed inset-0 z-[999999] flex items-center justify-center bg-white dark:bg-gray-900">
        <x-common.preloader />
    </div>

    <div class="min-h-screen xl:flex" x-show="loaded" style="display: none;">

        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">

            @include('layouts.app-header')

            <div class="p-4 mx-auto max-w-[1536px] md:p-6">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
