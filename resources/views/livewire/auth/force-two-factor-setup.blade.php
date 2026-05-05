<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <x-toast />
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-flux::logo fill="#0690FA" class="h-24" />
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
        <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10">
            @section('force-two-factor-setup')
                <div class="space-y-6">
                    @section('force-two-factor-setup.intro')
                        <div class="text-center">
                            <x-icon
                                name="shield-exclamation"
                                class="mx-auto size-12 text-indigo-600"
                            />
                            <h2 class="mt-2 text-2xl font-bold text-gray-900">
                                {{ __('Two-factor authentication required') }}
                            </h2>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Your administrator requires you to enable a second authentication factor before you can continue. Please choose a method below to finish setting up your account.') }}
                            </p>
                        </div>
                    @show

                    @if(is_null($method))
                        @section('force-two-factor-setup.choice')
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <button
                                    type="button"
                                    wire:click="selectTotp()"
                                    class="flex flex-col items-start space-y-3 rounded-lg border border-gray-300 p-6 text-left transition hover:border-indigo-500 hover:shadow-md"
                                >
                                    <x-icon
                                        name="device-phone-mobile"
                                        class="size-8 text-indigo-600"
                                    />
                                    <div
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        {{ __('Authenticator app') }}
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ __('Use an app like Google Authenticator, 1Password or Authy. The app generates a 6-digit code that changes every 30 seconds. You enter that code on every login.') }}
                                    </p>
                                </button>
                                <button
                                    type="button"
                                    wire:click="selectPasskey()"
                                    class="flex flex-col items-start space-y-3 rounded-lg border border-gray-300 p-6 text-left transition hover:border-indigo-500 hover:shadow-md"
                                >
                                    <x-icon
                                        name="finger-print"
                                        class="size-8 text-indigo-600"
                                    />
                                    <div
                                        class="text-base font-semibold text-gray-900"
                                    >
                                        {{ __('Passkey') }}
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ __('Use Touch ID, Face ID, Windows Hello or a hardware key. The passkey is bound to this device or your password manager and replaces typing a code.') }}
                                    </p>
                                </button>
                            </div>
                        @show
                    @elseif($method === \FluxErp\Enums\ForceTwoFactorMethodEnum::Totp)
                        @section('force-two-factor-setup.totp')
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ __('Set up your authenticator app') }}
                                </h3>
                                <ol
                                    class="list-inside list-decimal space-y-2 text-sm text-gray-700"
                                >
                                    <li>
                                        {{ __('Install an authenticator app on your phone (Google Authenticator, 1Password, Authy, ...)') }}
                                    </li>
                                    <li>
                                        {{ __('Scan the QR code below or enter the secret key manually.') }}
                                    </li>
                                    <li>
                                        {{ __('Enter the 6-digit code the app shows to confirm the setup.') }}
                                    </li>
                                </ol>
                                <div
                                    class="flex justify-center rounded-lg bg-white p-4"
                                    x-html="$wire.qrCodeSvg"
                                ></div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">
                                        {{ __('Or enter this key manually:') }}
                                    </p>
                                    <code
                                        class="mt-1 inline-block rounded bg-gray-100 px-2 py-1 font-mono text-sm"
                                    >
                                        {{ $secretKey }}
                                    </code>
                                </div>
                                <form
                                    class="space-y-4"
                                    wire:submit="confirmTotp()"
                                >
                                    <x-pin
                                        wire:model.live="confirmCode"
                                        :label="__('Verification code')"
                                        :length="6"
                                        numbers
                                        smart
                                    />
                                    <div class="flex justify-between gap-2">
                                        <x-button
                                            :text="__('Back')"
                                            color="secondary"
                                            flat
                                            wire:click="back()"
                                            type="button"
                                        />
                                        <x-button
                                            loading
                                            :text="__('Confirm and continue')"
                                            color="primary"
                                            type="submit"
                                        />
                                    </div>
                                </form>
                            </div>
                        @show
                    @elseif($method === \FluxErp\Enums\ForceTwoFactorMethodEnum::Passkey)
                        @section('force-two-factor-setup.passkey')
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ __('Register a passkey') }}
                                </h3>
                                <ol
                                    class="list-inside list-decimal space-y-2 text-sm text-gray-700"
                                >
                                    <li>
                                        {{ __('Give the passkey a name (for example "MacBook" or "iPhone").') }}
                                    </li>
                                    <li>
                                        {{ __('Click "Create" and follow the prompt of your operating system or password manager.') }}
                                    </li>
                                    <li>
                                        {{ __('Once the passkey is registered the page reloads and you can continue.') }}
                                    </li>
                                </ol>
                                <livewire:passkeys />
                                <div class="flex justify-between gap-2 pt-2">
                                    <x-button
                                        :text="__('Back')"
                                        color="secondary"
                                        flat
                                        wire:click="back()"
                                        type="button"
                                    />
                                    <x-button
                                        :text="__('Continue')"
                                        color="primary"
                                        wire:click="passkeyStored()"
                                    />
                                </div>
                            </div>
                        @show
                    @endif
                </div>
            @show
        </div>
    </div>
</div>
