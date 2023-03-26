<HTML class="h-full bg-white" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $client = \FluxErp\Models\Client::first();
@endphp
<x-layouts.head.head>
    <x-slot:title>
        {{ $client?->name . ' Portal' }}
    </x-slot:title>
    <style>
        {{ $client?->settings()->first()['settings']['custom_css'] ?? '' }}
    </style>
</x-layouts.head.head>
<body class="portal-login h-full">
<div class="md:text-portal-font-color absolute right-4 top-6 text-white">
    <a href="{{ $client?->website }}" target="_blank" class="flex items-center dark:text-gray-50">
        <x-heroicons name="arrow-up-right" class="h-4 w-4" />
        <div class="return-to-website pl-4 font-bold">
            {{ __('Return to website') }}
        </div>
    </a>
</div>
<div class="md:text-portal-font-color absolute left-4 top-6 text-white">
    {{ $client?->getFirstMedia('logo') }}
</div>
<div class="flex min-h-full justify-center">
    <div class="flex flex-1 flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
        <h1 class="pb-16 text-center text-5xl">{{ __('Login') }}</h1>
        <h2 class="text-center text-2xl">{{ __('For more transparency, quality and speed in all service processes') }}</h2>
        <div class="mx-auto w-full max-w-sm pt-16 lg:w-96">
            <div class="mt-8">
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        <x-badge negative :label="$error" />
                    @endforeach
                @endif
                <div class="mt-6">
                    <form action="{{ route('login') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name"
                                   class="block text-sm font-medium text-gray-700">{{ __('Username') }}</label>
                            <div class="mt-1">
                                <input autofocus id="login_name" name="login_name" autocomplete="email" required
                                       class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label for="password"
                                   class="block text-sm font-medium text-gray-700"> {{ __('Password') }} </label>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" autocomplete="current-password"
                                       required
                                       class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="remember-me"
                                       class="ml-2 block text-sm text-gray-900"> {{ __('Remember me') }} </label>
                            </div>
                            <div class="text-sm">
                                <a href="#"
                                   class="font-medium text-indigo-600 hover:text-indigo-500"> {{ __('Reset password') }}</a>
                            </div>
                        </div>
                        <div>
                            <button type="submit"
                                    class="flex w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">{{ __('Login') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</HTML>
