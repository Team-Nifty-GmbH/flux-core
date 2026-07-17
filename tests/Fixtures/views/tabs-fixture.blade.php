<div data-testid="tabs-fixture">
    <x-flux::tabs wire:model.live="activeTab" :$tabs />
    <button wire:click="refreshParent">Refresh</button>
    <button wire:click="switchModel">Switch Model</button>
</div>
