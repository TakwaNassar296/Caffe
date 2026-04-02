<x-filament::page >

    {{-- نخفي السايدبار باستخدام CSS --}}
    <style>
        aside.fi-sidebar {
            display: none !important;
        }

        .fi-main {
            margin-inline-start: 0 !important;
        }
    </style>

    {{ $slot }}
</x-filament::page >
