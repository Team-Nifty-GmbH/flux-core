@use('FluxErp\Enums\TimeFrameEnum')
<div class="flex flex-col md:flex-row gap-2">
    <div x-cloak x-show="!editGrid" class="flex flex-col md:flex-row gap-2 items-center text-sm">
        <x-select.styled
            class="p-2"
            wire:model.live="params.timeFrame"
            required
            :options="TimeFrameEnum::valuesLocalized()"
        />
        <div class="flex flex-col md:flex-row gap-2 items-center min-w-96" x-cloak x-show="$wire.params.timeFrame === 'Custom'">
            <x-date wire:model.live="params.start" :without-time="true"/>
            <div>
                <span class="px-2">{{ __('Till') }}</span>
            </div>
            <x-date wire:model.live="params.end" :without-time="true"/>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-2 items-center">
        <x-button color="secondary" light
            x-cloak
            x-show="!editGrid"
            x-on:click="isLoading ? pendingMessage : editGridMode(true)"
            icon="pencil"
            class="flex-shrink-0"
        />
        <div x-cloak x-show="editGrid" class="flex gap-2">
            <x-button color="secondary" light
                x-on:click="$modalOpen('widget-list')"
                class="flex-shrink-0"
                :text="__('Add')"
            />
            <x-button
                color="indigo"
                x-on:click="isLoading ? pendingMessage : save"
                :text="__('Save')"
                class="flex-shrink-0"
            />
            <x-button
                color="red"
                wire:flux-confirm.type.error="{{ __('wire:confirm.cancel.dashboard-edit') }}"
                wire:click="resetWidgets().then(() => {reInit().disable(); isLoading = false; editGridMode(false);})"
                class="flex-shrink-0"
                :text="__('Cancel')"
            />
        </div>
    </div>
</div>
