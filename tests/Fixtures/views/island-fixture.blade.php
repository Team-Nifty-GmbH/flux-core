<div>
    @island(name: 'island-fixture')
        <x-select.styled
            wire:model="choice"
            :label="__('Choice')"
            :options="[['label' => 'A', 'value' => 'a']]"
        />
        @island(name: 'island-fixture-nested')
            <x-select.styled
                wire:model="nestedChoice"
                :label="__('Nested Choice')"
                :options="[['label' => 'B', 'value' => 'b']]"
            />
        @endisland
    @endisland
</div>
