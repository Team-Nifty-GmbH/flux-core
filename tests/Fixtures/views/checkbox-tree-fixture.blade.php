<div data-testid="checkbox-tree-fixture">
    <x-flux::checkbox-tree
        :tree="json_encode($tree)"
        wire:model="selected"
        selectable
        multiselect
    />
</div>
