<x-modal :id="$target->modalName()">
    <div class="flex flex-col gap-4">
        <x-spinner />
        <x-input wire:model="target.name" :label="__('Title')" required />
        <x-date :label="__('Start')" wire:model="target.start_date" />
        <x-date :label="__('End')" wire:model="target.end_date" />
        <x-select.styled
            :label="__('Model Type')"
            wire:model="target.model_type"
            x-on:select="$wire.updateSelectableColumns($event.detail.select.value)"
            :options="$modelTypes"
        />
        <x-select.native
            :label="__('Timeframe Column')"
            wire:model="target.timeframe_column"
            :options="$timeframeColumns"
        />
        <x-select.styled
            :label="__('Aggregate Type')"
            wire:model="target.aggregate_type"
            x-on:select="$wire.updateAggregateColumnOptions($event.detail.select.value)"
            :options="$aggregateTypes"
        />
        <x-select.native
            :label="__('Aggregate Column')"
            wire:model="target.aggregate_column"
            :options="$aggregateColumns"
        />
        <x-number
            :label="__('Target Value')"
            wire:model="target.target_value"
        />
        <x-select.native
            :label="__('Owner Column')"
            wire:model="target.owner_column"
            :options="$ownerColumns"
        />
        <x-number :label="__('Priority')" wire:model="target.priority" />
        <x-select.styled
            :label="__('Users')"
            autocomplete="off"
            multiple
            wire:model="target.users"
            select="label:label|value:id"
            x-on:select="$wire.updateUsers()"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media'
                ],
            ]"
        />

        <x-toggle
            :label="__('Is Group Target')"
            wire:model="target.is_group_target"
        />

        <div x-cloak x-show="$wire.target.is_group_target">
            <div class="space-y-2">
                @foreach (\FluxErp\Models\User::whereIn('id', $target->users)->get() as $user)
                    <x-card class="flex items-center">
                        <div class="flex flex-1 items-center">
                            <img
                                class="h-8 w-8 rounded-full bg-gray-200 object-cover object-center"
                                src="{{ $user->getAvatarUrl() }}"
                            />
                            <span class="ml-2">{{ $user->name }}</span>
                        </div>

                        <div class="flex gap-2" style="flex: 0 0 55%">
                            <x-number
                                class="flex-1"
                                :label="__('Relative Target Share (%)')"
                                step="1"
                                min="0"
                                max="100"
                                wire:model.defer="target.user_shares.{{ $user->id }}.relative"
                            />
                            <x-number
                                class="flex-1"
                                :label="__('Absolute Target Share')"
                                step="1"
                                min="0"
                                wire:model.defer="target.user_shares.{{ $user->id }}.absolute"
                            />
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('{{ $target->modalName() }}')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $target->modalName() }}')})"
        />
    </x-slot>
</x-modal>
