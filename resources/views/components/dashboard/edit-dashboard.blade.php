@use('FluxErp\Enums\TimeFrameEnum')
<div class="flex flex-col md:flex-row gap-1.5">
    <div x-cloak x-show="! editGrid" class="flex flex-col md:flex-row gap-1.5 items-center text-sm">
        <x-select
            class="p-2"
            :options="TimeFrameEnum::valuesLocalized()"
            option-key-value
            wire:model.live="params.timeFrame"
            :clearable="false"
        />
        <div class="flex flex-col md:flex-row gap-1.5 items-center min-w-96" x-cloak x-show="$wire.params.timeFrame === 'Custom'">
            <x-datetime-picker wire:model.live="params.start" :without-time="true"/>
            <div>
                <span class="px-1.5">{{ __('Till') }}</span>
            </div>
            <x-datetime-picker wire:model.live="params.end" :without-time="true"/>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-1.5 items-center">
        @canAction(\FluxErp\Actions\Dashboard\UpdateDashboard::class)
            <x-button
                x-cloak
                x-show="!editGrid"
                x-on:click="isLoading ? pendingMessage : editGridMode(true)"
                icon="pencil"
                class="flex-shrink-0"
            />
            <div x-cloak x-show="editGrid" class="flex gap-1.5">
                <x-button
                    x-on:click="$openModal('widget-list')"
                    class="flex-shrink-0"
                    :label="__('Add')"
                />
                <x-button
                    primary
                    x-on:click="isLoading ? pendingMessage : save"
                    :label="__('Save')"
                    class="flex-shrink-0"
                />
                <x-button
                    negative
                    wire:flux-confirm.icon.error="{{ __('wire:confirm.cancel.dashboard-edit') }}"
                    wire:click="resetWidgets().then(() => {reInit().disable(); isLoading = false; editGridMode(false);})"
                    class="flex-shrink-0"
                    :label="__('Cancel')"
                />
            </div>
        @endCanAction
    </div>
</div>
