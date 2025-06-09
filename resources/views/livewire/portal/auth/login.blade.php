<div>
    @php
        $client = app(\FluxErp\Models\Client::class)->first();
    @endphp

    @section('password-reset-dialog')
    <x-modal id="password-reset">
        <x-card :header="__('Reset password')">
            <x-input
                wire:model="email"
                :label="__('Email')"
                name="reset-email"
                type="email"
                required
            />
            <x-slot:footer>
                <x-button
                    wire:click="resetPassword()"
                    color="indigo"
                    class="w-full"
                    :text="__('Reset password')"
                    x-on:click="$modalClose('password-reset')"
                ></x-button>
            </x-slot>
        </x-card>
    </x-modal>
    @show
    <x-slot:title>
        {{ $client?->name . ' Portal' }}
    </x-slot>
    <style>
        {{ $client?->settings()->first()['settings']['custom_css'] ?? '' }}
    </style>
    <div class="md:text-portal-font-color absolute top-6 right-4 text-white">
        <a
            href="{{ $client?->website }}"
            target="_blank"
            class="flex items-center dark:text-gray-50"
        >
            <x-icon name="arrow-up-right" class="h-4 w-4" />
            <div class="return-to-website pl-4 font-bold">
                {{ __('Return to website') }}
            </div>
        </a>
    </div>
    <div
        class="md:text-portal-font-color absolute top-6 left-4 max-h-80 max-w-80 text-white"
    >
        {{ $client?->getFirstMedia('logo') }}
    </div>
    <div class="flex min-h-full justify-center">
        <div
            class="flex flex-1 flex-col justify-center px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24"
        >
            @section('headline')
            <h1 class="pb-16 text-center text-5xl">{{ __('Login') }}</h1>
            <h2 class="text-center text-2xl">
                {{ __('For more transparency, quality and speed in all service processes') }}
            </h2>
            @show
            <div class="mx-auto w-full max-w-sm pt-16 lg:w-96">
                <div class="mt-8">
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <x-badge color="red" :text="$error" />
                        @endforeach
                    @endif

                    <div class="mt-6">
                        @section('login-form')
                        <form class="flex flex-col gap-6" wire:submit="login()">
                            <x-input
                                id="email"
                                wire:model="email"
                                :label="__('Email')"
                                name="email"
                                type="email"
                                required
                                autofocus
                            />
                            <x-password
                                wire:model="password"
                                :text="__('Password')"
                                id="password"
                                name="password"
                            />
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <a
                                        x-on:click="$modalOpen('password-reset')"
                                        class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-500"
                                    >
                                        {{ __('Reset password') }}
                                    </a>
                                </div>
                            </div>
                            <div
                                x-transition
                                x-cloak
                                x-show="$wire.email && $wire.password"
                            >
                                <x-button
                                    loading
                                    color="indigo"
                                    class="w-full"
                                    :text="__('Login')"
                                    type="submit"
                                    dusk="login-button"
                                ></x-button>
                            </div>
                            <div x-transition x-cloak x-show="$wire.email">
                                <x-button
                                    loading
                                    color="indigo"
                                    class="w-full"
                                    :text="__('Send Login Link')"
                                    type="submit"
                                    dusk="magic-login-button"
                                ></x-button>
                            </div>
                        </form>
                        @show
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
