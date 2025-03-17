<div
    wire:ignore
    x-data="{ height: 0 }"
    x-resize.document="height = $height - 126"
    x-bind:style="{ height: height + 'px' }"
>
    <livewire:features.calendar.flux-calendar />
</div>
