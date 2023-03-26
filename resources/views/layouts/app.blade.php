<!DOCTYPE html>
<html class="soft-scrollbar h-full text-sm" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head/>
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300">
    <x-notifications z-index="z-50"></x-notifications>
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
        <div id="nav">
            <livewire:navigation/>
        </div>
        <div class="md:pl-20">
            <div class="p-6">
                <livewire:features.search-bar />
            </div>
            <main class="px-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
