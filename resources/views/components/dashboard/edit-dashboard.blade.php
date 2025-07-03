@use('FluxErp\Enums\TimeFrameEnum')
<div class="flex flex-col gap-2 md:flex-row">
    @if ($hasTimeSelector)
        <div
            x-cloak
            x-show="!editGrid"
            class="flex flex-col items-center gap-2 text-sm md:min-w-96 md:flex-row"
        >
            <div class="w-full grow">
                <x-select.styled
                    class="p-2"
                    wire:model.live="params.timeFrame"
                    required
                    :options="TimeFrameEnum::valuesLocalized()"
                />
            </div>
            <div
                class="flex min-w-96 flex-col items-center gap-2 md:flex-row"
                x-cloak
                x-show="$wire.params.timeFrame === 'Custom'"
            >
                <x-date wire:model.live="params.start" :without-time="true" />
                <div>
                    <span class="px-2">{{ __('Till') }}</span>
                </div>
                <x-date wire:model.live="params.end" :without-time="true" />
            </div>
        </div>
    @endif

    @if ($canEdit)
        <div class="flex flex-col items-center gap-2 md:flex-row">
            <x-button
                color="secondary"
                loading
                light
                x-cloak
                x-show="!editGrid"
                x-on:click="editGridMode(true)"
                icon="pencil"
                class="flex-shrink-0"
            />
            <div x-cloak x-show="editGrid" class="flex gap-2">
                <x-button
                    color="secondary"
                    light
                    x-on:click="$modalOpen('widget-list')"
                    class="flex-shrink-0"
                    :text="__('Add')"
                />
                <x-button
                    color="indigo"
                    loading
                    x-on:click="save"
                    :text="__('Save')"
                    class="flex-shrink-0"
                />
                <x-button
                    color="red"
                    loading
                    wire:flux-confirm.type.error="{{ __('wire:confirm.cancel.dashboard-edit') }}"
                    wire:click="resetWidgets().then(onPostReset.bind($data))"
                    class="flex-shrink-0"
                    :text="__('Cancel')"
                />
            </div>
        </div>
    @endif
</div>
