<x-modal
    id="edit-serial-number-range-modal"
    :title="__('Serial Number Range')"
>
    <div class="flex flex-col gap-1.5">
        <div
            class="flex flex-col gap-1.5"
            x-cloak
            x-show="! $wire.serialNumberRange.id"
        >
            <x-select.styled
                wire:model="serialNumberRange.model_type"
                :label="__('Model')"
                :options="$models"
            />
            <x-select.styled
                wire:model="serialNumberRange.tenant_id"
                :label="__('Tenant')"
                select="label:name|value:id"
                :options="$tenants"
            />
        </div>
        <x-input wire:model="serialNumberRange.type" :label="__('Type')" />
        <div x-show="$wire.serialNumberRange.id" x-cloak>
            <x-number
                step="1"
                min="0"
                wire:model.number="serialNumberRange.current_number"
                :label="__('Current Number')"
            />
        </div>
        <x-number
            step="1"
            min="1"
            wire:model.number="serialNumberRange.length"
            :label="__('Length')"
        />
        <x-input wire:model="serialNumberRange.prefix" :label="__('Prefix')" />
        <x-input wire:model="serialNumberRange.suffix" :label="__('Suffix')" />
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Available Placeholders') }}
            </div>
            <div class="grid grid-cols-1 gap-1 text-xs">
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_day</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current day (e.g. :example)', ['example' => date('d')]) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_week</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current week (e.g. :example)', ['example' => date('W')]) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_month</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current month (e.g. :example)', ['example' => date('m')]) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_quarter</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current quarter (e.g. :example)', ['example' => ceil(date('n') / 3)]) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_year</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current year (e.g. :example)', ['example' => date('Y')]) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <code class="rounded bg-gray-200 px-1.5 py-0.5 font-mono text-gray-800 dark:bg-gray-700 dark:text-gray-200">:current_year_short</code>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Current year short (e.g. :example)', ['example' => date('y')]) }}</span>
                </div>
            </div>
        </div>
        <x-textarea
            wire:model="serialNumberRange.description"
            :label="__('Description')"
        />
        <div class="mt-2">
            <x-toggle
                wire:model="serialNumberRange.is_pre_filled"
                :label="__('Is Pre Filled')"
            />
        </div>
        <x-toggle
            wire:model="serialNumberRange.stores_serial_numbers"
            :label="__('Stores Serial Numbers')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-serial-number-range-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('edit-serial-number-range-modal')})"
        />
    </x-slot>
</x-modal>
