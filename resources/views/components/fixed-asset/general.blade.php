<x-card class="w-full">
    <div x-bind:class="!isEditing && 'pointer-events-none'">
        <x-flux::fixed-asset.fields />
    </div>
    <x-slot:footer>
        <div class="pointer-events-none opacity-60">
            <x-number
                wire:model="fixedAsset.book_value"
                :label="__('Book Value')"
                step="0.01"
            />
        </div>
    </x-slot:footer>
</x-card>
