<div class="flex flex-col gap-4">
    {{ $this->{$this->getFormPropertyName()}->autoRender($__data) }}
    <div class="flex justify-end">
        <x-button wire:click="save" :text="__('Save')" />
    </div>
</div>
