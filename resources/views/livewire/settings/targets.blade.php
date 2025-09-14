<x-modal
    size="3xl"
    :id="$target->modalName()"
>
    <div
        x-data="{
            users: @js($users),
            selectedUsers: $wire.entangle('target.users'),
            filteredUsers: [],
            userShares: $wire.entangle('target.user_shares'),

            init() {
                if (!this.userShares || typeof this.userShares !== 'object') {
                    this.userShares = {};
                }

                this.filterUsers();
                this.$watch('selectedUsers', () => this.filterUsers());
            },

            filterUsers() {
                const selected = Array.isArray(this.selectedUsers) ? this.selectedUsers : [];
                const selectedIds = selected.map(v => String(v));

                this.filteredUsers = selectedIds.length === 0
                    ? []
                    : this.users.filter(u => selectedIds.includes(String(u.id)));

                this.filteredUsers.forEach(u => {
                    if (!this.userShares[String(u.id)]) {
                        this.userShares[String(u.id)] = { relative: 0, absolute: 0 };
                    } else {
                        if (this.userShares[String(u.id)].relative === undefined) {
                            this.userShares[String(u.id)].relative = 0;
                        }
                        if (this.userShares[String(u.id)].absolute === undefined) {
                            this.userShares[String(u.id)].absolute = 0;
                        }
                    }
                });
            },

            updateUserShare(userId, key, value) {
                const id = String(userId);
                const num = value === '' ? 0 : Number(value);

                if (!this.userShares[id]) this.userShares[id] = { relative: 0, absolute: 0 };

                if (key === 'relative') {
                    this.userShares[id].relative = isNaN(num) ? null : num;
                    this.userShares[id].absolute = null;
                } else {
                    this.userShares[id].absolute = isNaN(num) ? null : num;
                    this.userShares[id].relative = null;
                }
            }
        }"
        x-init="init()"
        class="flex flex-col gap-3"
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

        <x-number :label="__('Target Value')" wire:model="target.target_value" />
        <x-select.native :label="__('Owner Column')" wire:model="target.owner_column" :options="$ownerColumns" />
        <x-number :label="__('Priority')" wire:model="target.priority" />
        <x-toggle :label="__('Is Group Target')" wire:model="target.is_group_target" />

        <x-select.styled
            :label="__('Users')"
            autocomplete="off"
            multiple
            wire:model="target.users"
            select="label:label|value:id"
            x-on:select="filterUsers()"
            x-on:remove="filterUsers()"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
            ]"
        />

        <x-toggle :label="__('Use absolute shares')" x-model="$wire.useAbsoluteShares" />

        <div class="space-y-2">
            <template x-for="user in filteredUsers" :key="user.id">
                <x-card class="flex items-center">
                    <div class="flex flex-1 items-center">
                        <span class="ml-2" x-text="user.name"></span>
                    </div>

                    <div class="flex gap-2 w-1/3">
                        <div x-cloak x-show="!$wire.useAbsoluteShares">
                            <x-number
                                class="flex-1"
                                :label="__('Relative Target Share (%)')"
                                step="1"
                                min="0"
                                max="100"
                                x-model.number="userShares[String(user.id)].relative"
                                x-on:change="updateUserShare(user.id, 'relative', $event.target.value)"
                            />
                        </div>
                        <div x-cloak x-show="$wire.useAbsoluteShares">
                            <x-number
                                class="flex-1"
                                :label="__('Absolute Target Share')"
                                step="1"
                                min="0"
                                x-model.number="userShares[String(user.id)].absolute"
                                x-on:change="updateUserShare(user.id, 'absolute', $event.target.value)"
                            />
                        </div>
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
            wire:click="save().then(success => { if(success) $modalClose('{{ $target->modalName() }}') })"
        />
    </x-slot>
</x-modal>
