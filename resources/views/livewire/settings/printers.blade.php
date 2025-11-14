<x-modal
    id="printer-bridge-config-modal"
    size="3xl"
    :title="__('Printer Bridge Configuration')"
    x-data="{ configGenerated: false }"
    x-on:open="configGenerated = false"
>
    <div class="flex flex-col gap-4">
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
                :label="__('Printer Check Interval (seconds)')"
                :hint="__('How often to check for new printers')"
                min="1"
            />

            <x-number
                wire:model.number="jobCheckInterval"
                :label="__('Job Check Interval (seconds)')"
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

        <div class="flex gap-2">
            <x-button
                color="indigo"
                :text="__('Generate Configuration')"
                icon="cog"
                wire:click="generateBridgeConfig().then((success) => { if(success) configGenerated = true })"
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
                        navigator.clipboard.writeText($wire.bridgeConfig ? JSON.stringify($wire.bridgeConfig, null, 2) : '');
                        $wire.toast().success('{{ __('Copied!') }}', '{{ __('Configuration copied to clipboard') }}').send();
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