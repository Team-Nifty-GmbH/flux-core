@use('FluxErp\Enums\TimeFrameEnum')
<div class="flex items-center gap-4">
    @if (in_array(\FluxErp\Traits\Livewire\Dashboard\SupportsGrouping::class, class_uses_recursive($this)) && $canEdit)
        <template x-for="in allGroups group">
            <div class="relative">
                <x-button
                    wire:loading.attr="disabled"
                    class="!dark:text-secondary-400 border-b-2 border-b-transparent !text-secondary-600 focus:!ring-0 focus:!ring-offset-0"
                    flat
                    x-bind:class="{'!border-b-primary-600 !rounded-b-none': (group === null && $wire.group === null) || (group !== null && group === $wire.group)}"
                    x-on:click="$wire.set('group', group)"
                >
                    <x-slot:text>
                        <span x-text="group ?? '{{ __('Default') }}'"></span>
                    </x-slot>
                </x-button>
                <x-button.circle
                    x-show="editGrid && group !== null"
                    x-cloak
                    icon="x-mark"
                    wire:loading.attr="disabled"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Group')]) }}"
                    wire:click="deleteGroup(group)"
                    class="absolute -right-1 -top-1 h-4 w-4 cursor-pointer bg-red-500 text-white hover:bg-red-600"
                    xs
                />
            </div>
        </template>

        <x-button.circle
            x-show="editGrid"
            x-cloak
            wire:loading.attr="disabled"
            x-on:click="$modalOpen('create-group-modal')"
            class="h-6 w-6 cursor-pointer bg-green-500 text-white hover:bg-green-600"
            size="sm"
        >
            <x-icon name="plus" class="h-4 w-4" />
        </x-button.circle>
    @endif
</div>
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

<x-modal
    id="create-group-modal"
    :title="__('Create New Group')"
    x-on:open="$focusOn('new-group-name')"
>
    <x-input
        id="new-group-name"
        x-model="newGroupName"
        :label="__('Group Name')"
    />

    <x-slot:footer>
        <x-button
            color="secondary"
            x-on:click="$modalClose('create-group-modal')"
            :text="__('Cancel')"
        />
        <x-button
            x-on:click="
                if (newGroupName.trim()) {
                    addNewGroup(newGroupName.trim());
                    $wire.set('group', newGroupName.trim());
                    newGroupName = '';
                    $modalClose('create-group-modal');
                }
            "
            :text="__('Save')"
        />
    </x-slot>
</x-modal>
