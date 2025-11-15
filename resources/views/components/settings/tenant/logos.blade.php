<div class="flex flex-col gap-6">
    <x-flux::features.media.upload-form-object
        :label="__('Logo')"
        wire:model="logo"
        :multiple="false"
        accept="image/jpeg, image/png, image/svg+xml"
    />
    <x-flux::features.media.upload-form-object
        :label="__('Logo small')"
        wire:model="logoSmall"
        :multiple="false"
        accept="image/jpeg, image/png, image/svg+xml"
    />
</div>
