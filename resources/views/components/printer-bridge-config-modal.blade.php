<x-modal
    id="printer-bridge-config-modal"
    size="3xl"
    :title="__('Printer Bridge Configuration')"
>
    <div
        class="flex flex-col gap-4"
        x-data="{ configGenerated: false, showConfirmation: false }"
        x-on:config-generated.window="configGenerated = true; showConfirmation = false"
        x-on:confirm-token-regeneration.window="showConfirmation = true"
    >
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Configure the settings for your printer bridge device. Fill in the options below and generate the configuration.') }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input
                wire:model="instanceName"
                :label="__('Instance Name')"
                :hint="__('Unique identifier for this bridge instance')"
            />

            <x-number
                wire:model.number="printerCheckInterval"
                :label="__('Printer Check Interval (minutes)')"
                :hint="__('How often to check for new printers')"
                min="1"
            />

            <x-number
                wire:model.number="jobCheckInterval"
                :label="__('Job Check Interval (minutes)')"
                :hint="__('How often to check for new print jobs')"
                min="1"
            />

            <x-number
                wire:model.number="apiPort"
                :label="__('API Port')"
                :hint="__('Port for the bridge API server')"
                min="1"
                max="65535"
            />
        </div>

        <x-toggle
            wire:model.boolean="reverbDisabled"
            :label="__('Disable Reverb (WebSocket)')"
            :hint="__('Enable this if you want to disable real-time updates via WebSocket')"
        />

        <div x-show="showConfirmation" x-cloak class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('Token Already Exists') }}
                    </h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        {{ __('A token with this instance name already exists. Regenerating will invalidate the old token. Do you want to continue?') }}
                    </p>
                    <div class="mt-3 flex gap-2">
                        <x-button
                            color="warning"
                            size="sm"
                            :text="__('Yes, Regenerate Token')"
                            wire:click="confirmRegeneration"
                        />
                        <x-button
                            color="secondary"
                            size="sm"
                            light
                            :text="__('Cancel')"
                            x-on:click="showConfirmation = false"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <x-button
                color="indigo"
                :text="__('Generate Configuration')"
                icon="cog"
                wire:click="generateBridgeConfig"
            />
        </div>

        <div x-show="configGenerated" x-cloak class="mt-4">
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Generated Configuration') }}
                </label>
                <x-button
                    color="primary"
                    size="sm"
                    :text="__('Copy to Clipboard')"
                    icon="clipboard"
                    x-on:click="
                        const configText = $wire.bridgeConfig ? JSON.stringify($wire.bridgeConfig, null, 2) : '';
                        navigator.clipboard.writeText(configText)
                            .then(() => {
                                $wire.copyToClipboard();
                            })
                            .catch(() => {
                                $wire.showClipboardError();
                            });
                    "
                />
            </div>

            <div class="relative">
                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-xs font-mono max-h-96"><code x-text="$wire.bridgeConfig ? JSON.stringify($wire.bridgeConfig, null, 2) : ''"></code></pre>
            </div>

            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Copy this configuration and paste it into your printer bridge device configuration file.') }}
            </div>
        </div>
    </div>

    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Close')"
            x-on:click="$modalClose('printer-bridge-config-modal')"
        />
    </x-slot:footer>
</x-modal>