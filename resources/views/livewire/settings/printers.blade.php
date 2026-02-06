<x-modal id="edit-printer-modal" :title="__('Edit Printer')">
    <div class="flex flex-col gap-4">
        <x-input wire:model="printerForm.alias" :label="__('Alias')" />
        <x-toggle
            wire:model.boolean="printerForm.is_visible"
            :label="__('Visible')"
            :hint="__('Show this printer in selection lists for users')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-printer-modal')"
        />
        <x-button
            color="primary"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-printer-modal')})"
        />
    </x-slot>
</x-modal>

<x-modal id="delete-spooler-modal" :title="__('Delete Spooler')">
    <div class="flex flex-col gap-4">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Select a spooler to delete. This will delete all printers associated with this spooler and invalidate the corresponding API token.') }}
        </div>
        <x-select.styled
            wire:model="deleteSpoolerName"
            :label="__('Spooler Name')"
            :options="$spoolerNames"
            select="label:label|value:value"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('delete-spooler-modal')"
        />
        <x-button
            color="red"
            :text="__('Delete')"
            wire:click="deleteSpooler().then((success) => { if(success) $modalClose('delete-spooler-modal')})"
            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Spooler')]) }}"
        />
    </x-slot>
</x-modal>

<x-modal
    id="printer-bridge-config-modal"
    size="3xl"
    :title="__('Printer Bridge Configuration')"
>
    <div
        class="flex flex-col gap-4"
        x-data="{ configGenerated: false }"
        x-on:config-generated.window="configGenerated = true"
    >
        <div class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Configure the settings for your printer bridge device. Fill in the options below and generate the configuration.') }}
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-input
                wire:model="configForm.instance_name"
                :label="__('Instance Name')"
                :hint="__('Unique identifier for this bridge instance')"
            />

            <x-number
                wire:model.number="configForm.printer_check_interval"
                :label="__('Printer Check Interval (minutes)')"
                :hint="__('How often to check for new printers')"
                min="1"
            />

            <x-number
                wire:model.number="configForm.job_check_interval"
                :label="__('Job Check Interval (minutes)')"
                :hint="__('How often to check for new print jobs')"
                min="1"
            />

            <x-number
                wire:model.number="configForm.api_port"
                :label="__('API Port')"
                :hint="__('Port for the bridge API server')"
                min="1"
                max="65535"
            />
        </div>

        <x-toggle
            wire:model.boolean="configForm.reverb_disabled"
            :label="__('Disable Reverb (WebSocket)')"
            :hint="__('Enable this if you want to disable real-time updates via WebSocket')"
        />

        <div class="flex gap-2">
            <x-button
                color="indigo"
                :text="__('Generate Configuration')"
                icon="cog"
                wire:click="generateBridgeConfig"
            />
        </div>

        <div x-show="configGenerated" x-cloak class="mt-4">
            <div class="mb-2 flex items-center justify-between">
                <label
                    class="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    {{ __('Generated Configuration') }}
                </label>
                <x-button
                    color="primary"
                    size="sm"
                    :text="__('Copy to Clipboard')"
                    icon="clipboard"
                    x-on:click="
                        const configText = $wire.configForm.bridge_config ? JSON.stringify($wire.configForm.bridge_config, null, 2) : '';
                        navigator.clipboard.writeText(configText)
                            .then(() => {
                                $interaction('toast')
                                    .success('{{ __('Copied!') }}', '{{ __('Configuration copied to clipboard') }}')
                                    .send();
                            })
                            .catch(() => {
                                $interaction('toast')
                                    .error('{{ __('Error') }}', '{{ __('Failed to copy to clipboard. Please try again.') }}')
                                    .send();
                            });
                    "
                />
            </div>

            <div class="relative">
                <pre
                    class="max-h-96 overflow-x-auto rounded-lg bg-gray-900 p-4 font-mono text-xs text-gray-100"
                ><code x-text="
                    $wire.configForm.bridge_config
                        ? JSON.stringify($wire.configForm.bridge_config, null, 2)
                        : ''
                "></code></pre>
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
    </x-slot>
</x-modal>
