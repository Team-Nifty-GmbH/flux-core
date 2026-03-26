<div data-testid="tabs-fixture">
    <x-flux::tabs wire:model.live="activeTab" :$tabs />
    <button wire:click="refreshParent">Refresh</button>
</div>
