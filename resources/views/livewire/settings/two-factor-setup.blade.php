<div>
    @section('two-factor-setup')
        <x-card>
            <div class="space-y-4">
                <div
                    class="flex items-center justify-between border-b pb-4 dark:border-gray-700"
                >
                    <div>
                        <h3 class="text-lg font-semibold dark:text-white">
                            {{ __('Two-Factor Authentication') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Secure your account with a TOTP authenticator app') }}
                        </p>
                    </div>
                    <div>
                        <div x-show="$wire.isTwoFactorEnabled" x-cloak>
                            <x-badge color="emerald" :text="__('Enabled')" />
                        </div>
                        <div x-show="!$wire.isTwoFactorEnabled" x-cloak>
                            <x-badge color="gray" :text="__('Disabled')" />
                        </div>
                    </div>
                </div>

                <div x-show="$wire.showSetup" x-cloak>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.) and enter the verification code below.') }}
                        </p>
                        <div
                            class="flex justify-center rounded-lg bg-white p-4"
                            x-html="$wire.qrCodeSvg"
                        ></div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Or enter this key manually:') }}
                            </p>
                            <code
                                class="mt-1 inline-block rounded bg-gray-100 px-3 py-1 font-mono text-sm dark:bg-gray-700 dark:text-gray-200"
                                x-text="$wire.secretKey"
                            ></code>
                        </div>
                        <form wire:submit="confirmSetup()" class="space-y-4">
                            <div class="flex justify-center">
                                <x-pin
                                    wire:model.live="confirmCode"
                                    :label="__('Verification Code')"
                                    :length="6"
                                    numbers
                                    smart
                                />
                            </div>
                            <div class="flex justify-end gap-2">
                                <x-button
                                    :text="__('Cancel')"
                                    color="secondary"
                                    flat
                                    wire:click="cancelSetup()"
                                />
                                <x-button
                                    :text="__('Verify')"
                                    color="primary"
                                    type="submit"
                                />
                            </div>
                        </form>
                    </div>
                </div>

                <div
                    x-show="!$wire.showSetup && $wire.isTwoFactorEnabled"
                    x-cloak
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Your account is protected with two-factor authentication.') }}
                        </p>
                        @if(! $isForced)
                            <x-button
                                :text="__('Disable')"
                                color="red"
                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Two-Factor Authentication')]) }}"
                                wire:click="disableTwoFactor()"
                            />
                        @else
                            <p class="text-xs text-amber-600 dark:text-amber-400">
                                {{ __('Two-factor authentication is required by your administrator.') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div
                    x-show="!$wire.showSetup && !$wire.isTwoFactorEnabled"
                    x-cloak
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Add an extra layer of security to your account.') }}
                        </p>
                        <x-button
                            :text="__('Enable')"
                            color="primary"
                            wire:click="startSetup()"
                        />
                    </div>
                    @if($isForced)
                        <p class="text-sm text-amber-600 dark:text-amber-400">
                            {{ __('Your administrator requires you to enable two-factor authentication.') }}
                        </p>
                    @endif
                </div>
            </div>
        </x-card>
    @show

    @section('passkey-management')
        <div x-show="browserSupportsWebAuthn" x-cloak>
            <x-card class="mt-6">
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between border-b pb-4 dark:border-gray-700"
                    >
                        <div>
                            <h3 class="text-lg font-semibold dark:text-white">
                                {{ __('Passkeys') }}
                            </h3>
                            <p
                                class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('Use passkeys to sign in without a password') }}
                            </p>
                        </div>
                    </div>
                    <livewire:passkeys />
                </div>
            </x-card>
        </div>
    @show
</div>
