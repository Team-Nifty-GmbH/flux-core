<x-card class="w-full">
    <x-flux::features.media.upload-form-object
        :label="__('Contract')"
        wire:model="contract"
        :multiple="false"
        accept="application/pdf, image/jpeg, image/png"
    />
    <x-slot:footer>
        <x-button
            color="indigo"
            :text="__('Save')"
            loading="saveContract"
            x-on:click="$wire.saveContract()"
        />
    </x-slot:footer>
</x-card>
