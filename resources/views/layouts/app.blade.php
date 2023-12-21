<!DOCTYPE html>
@props(['navigation' => false])
<html class="soft-scrollbar h-full text-sm" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head/>
</head>
<body class="dark:bg-secondary-900 h-full bg-slate-50 transition duration-300">
    <x-notifications z-index="z-50"></x-notifications>
    <x-dialog z-index="z-40" blur="md" align="center"/>
    <x-dialog z-index="z-40" blur="md" align="center" id="prompt">
        <x-input id="prompt-value" />
    </x-dialog>
    <div x-data="{ open: false }" x-on:keydown.window.escape="open = false" class="flex h-screen w-full flex-col">
        @if(auth()->check() && method_exists(auth()->guard(), 'getName'))
            @php($navigation = true)
            @persist('navigation')
                <div id="nav">
                    <livewire:navigation/>
                </div>
            @endpersist
        @endif
        <div @if($navigation) class="md:pl-20" @endif>
            <main @if($navigation) class="px-1.5 md:px-8 pb-1.5 md:pb-8" @endif>
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
