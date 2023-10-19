<!DOCTYPE html>
<html class="soft-scrollbar h-full text-sm" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head/>
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300">
    <x-notifications z-index="z-50"></x-notifications>
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
        @if(auth()->check())
            @persist('navigation')
                <div id="nav">
                    <livewire:navigation/>
                </div>
            @endpersist
        @endif
        <div class="md:pl-20">
            <main class="px-1.5 md:px-8 pb-1.5 md:pb-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
