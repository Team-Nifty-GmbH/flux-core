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
            loading="save"
            :text="__('Save')"
            x-on:click="$wire.save()"
        />
    </x-slot:footer>
</x-card>
