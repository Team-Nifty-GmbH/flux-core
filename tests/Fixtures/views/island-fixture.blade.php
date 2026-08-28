<div>
    @island(name: 'island-fixture')
        <x-select.styled
            wire:model="choice"
            :label="__('Choice')"
            :options="[['label' => 'A', 'value' => 'a']]"
        />
    @endisland
</div>
