<x-modal size="3xl" :id="$target->modalName()" :title="__('Target')">
    <div
        x-data="{
            addUser(user) {
                if (
                    ! $wire.target.users.find(
                        (targetUser) => targetUser.user_id === user.id,
                    )
                ) {
                    $wire.target.users.push({
                        user_id: user.id,
                        label: user.label,
                        target_share: null,
                        is_percentage: null,
                    })
                }
            },

            removeUser(user) {
                $wire.target.users = $wire.target.users.filter(
                    (targetUser) => targetUser.user_id !== user.id,
                )
            },
        }"
        class="flex flex-col gap-4"
    >
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
        <x-toggle
            :label="__('Is Group Target')"
            wire:model="target.is_group_target"
        />

        <x-select.styled
            :label="__('Users')"
            multiple
            wire:model="selectedUserIds"
            x-on:select="addUser($event.detail.select)"
            x-on:remove="removeUser($event.detail.select)"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
            ]"
        />

        <div class="space-y-2">
            <template x-for="user in $wire.target.users" :key="user.user_id">
                <x-card class="flex items-center">
                    <div class="flex flex-1 items-center">
                        <span class="ml-2" x-text="user.label"></span>
                    </div>

                    <div class="flex w-1/3 items-center gap-4">
                        <x-toggle
                            label="{{ __('Percent') }}"
                            x-model="user.is_percentage"
                        />
                        <x-number x-model.number="user.target_share" />
                    </div>
                </x-card>
            </template>
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
