<div
    class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8"
    x-data
>
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-flux::logo fill="#0690FA" class="h-24" />
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <div class="mt-6">
                @section('password-reset-dialog')
                <x-modal id="password-reset" :title="__('Reset password')">
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
                </x-modal>
                @show
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
                        :label="__('Password')"
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
