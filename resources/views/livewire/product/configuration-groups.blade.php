<div class="flex flex-col gap-4">
    @foreach ($warnings as $warning)
        <x-alert color="amber" :text="$warning" />
    @endforeach

    <x-card :header="__('Configuration')">
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <x-select.styled
                    wire:model="groupId"
                    :label="__('Product Configuration Group')"
                    select="label:name|value:id"
                    :options="$groups"
                />
            </div>
            <x-button
                color="indigo"
                icon="plus"
                :text="__('Add group')"
                loading="attach"
                wire:click="attach()"
            />
        </div>
    </x-card>

    @foreach ($assignments as $index => $assignment)
        <x-card
            :header="$assignment['name']"
            wire:key="assignment-{{ $assignment['pivot_id'] }}"
        >
            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-4">
                    <x-toggle
                        wire:model="assignments.{{ $index }}.is_required"
                        :label="__('Required')"
                    />
                    <div class="w-32">
                        <x-number
                            wire:model="assignments.{{ $index }}.sort"
                            :label="__('Sort')"
                            min="0"
                        />
                    </div>
                </div>
                <x-label :label="__('Default selection')" />
                @foreach ($assignment['items'] as $item)
                    <div
                        class="flex items-center justify-between gap-4"
                        wire:key="default-{{ $item['id'] }}"
                    >
                        <span>{{ $item['name'] }}</span>
                        <div class="w-32">
                            <x-number
                                wire:model="defaults.{{ $item['id'] }}"
                                min="0"
                                :max="$item['max_amount']"
                            />
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <x-button
                        color="red"
                        icon="trash"
                        :text="__('Delete')"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Product Configuration Group')]) }}"
                        wire:click="detach({{ $assignment['pivot_id'] }})"
                    />
                </div>
            </div>
        </x-card>
    @endforeach

    <div class="flex justify-end">
        <x-button
            color="indigo"
            :text="__('Save')"
            loading="save"
            wire:click="save()"
        />
    </div>
</div>
