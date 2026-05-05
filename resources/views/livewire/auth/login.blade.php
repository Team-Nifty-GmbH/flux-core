<div
    class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8"
    x-data
>
    <x-toast />
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-flux::logo fill="#0690FA" class="h-24" />
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            <div class="mt-6">
                @section('password-reset-dialog')
                    <x-modal id="password-reset" :title="__('Reset password')">
                        <x-input
                            id="reset-email"
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
                                x-on:click="$tsui.close.modal('password-reset')"
                            ></x-button>
                        </x-slot:footer>
                    </x-modal>
                @show
                @section('totp-challenge')
                    <div x-show="$wire.showTotpChallenge" x-cloak>
                        <form
                            class="flex flex-col gap-6"
                            wire:submit="verifyTotpCode()"
                        >
                            <div class="text-center">
                                <x-icon
                                    name="shield-check"
                                    class="mx-auto size-12 text-indigo-600"
                                />
                                <h3
                                    class="mt-2 text-lg font-semibold text-gray-900"
                                >
                                    {{ __('Two-Factor Authentication') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Enter the code from your authenticator app') }}
                                </p>
                            </div>
                            <div class="flex justify-center">
                                <x-pin
                                    id="totp-code"
                                    wire:model="totpCode"
                                    :length="6"
                                    numbers
                                    smart
                                    autofocus
                                />
                                @error('totpCode')
                                    <p class="text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <x-button
                                loading
                                color="indigo"
                                class="w-full"
                                :text="__('Verify')"
                                type="submit"
                            />
                            <x-button
                                color="secondary"
                                flat
                                class="w-full"
                                :text="__('Cancel')"
                                wire:click="cancelTotpChallenge()"
                            />
                        </form>
                    </div>
                @show
                @section('login-form')
                    <div x-show="!$wire.showTotpChallenge" x-cloak>
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
                                <x-toggle
                                    wire:model="remember"
                                    :label="__('Remember me')"
                                />
                                <div class="text-sm">
                                    <a
                                        x-on:click="
                                            $tsui.open.modal('password-reset')
                                        "
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
                            @if(app(\FluxErp\Settings\SecuritySettings::class)->magic_login_links_enabled)
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
                            @endif
                        </form>
                        @section('passkey-login')
                            @if(Route::hasMacro('passkeys'))
                                <div
                                    class="mt-6"
                                    x-show="browserSupportsWebAuthn"
                                    x-cloak
                                >
                                    <div class="relative">
                                        <div
                                            class="absolute inset-0 flex items-center"
                                        >
                                            <div
                                                class="w-full border-t border-gray-300"
                                            ></div>
                                        </div>
                                        <div
                                            class="relative flex justify-center text-sm"
                                        >
                                            <span
                                                class="bg-white px-2 text-gray-500"
                                            >
                                                {{ __('Or') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <x-authenticate-passkey />
                                    </div>
                                </div>
                            @endif
                        @show
                    </div>
                @show

                <div
                    x-cloak
                    x-show="window.nuxbeAppBridge"
                    class="mt-4 text-center"
                >
                    <x-button
                        :text="__('Change Server')"
                        color="secondary"
                        flat
                        x-on:click="window.nuxbeAppBridge.changeServer()"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
