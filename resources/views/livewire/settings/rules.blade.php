<div>
    <x-modal id="edit-rule-modal" size="xl">
        <x-slot:title>
            {{ $ruleForm->id ? __('Edit Rule') : __('New Rule') }}
        </x-slot:title>

        <div class="flex flex-col gap-4">
            <x-input wire:model="ruleForm.name" :label="__('Name')" />
            <x-input wire:model="ruleForm.description" :label="__('Description')" />
            <div class="grid grid-cols-2 gap-4">
                <x-number wire:model="ruleForm.priority" :label="__('Priority')" />
                <x-toggle wire:model="ruleForm.is_active" :label="__('Active')" />
            </div>

            @if($ruleForm->id)
                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold mb-2">{{ __('Conditions') }}</h3>
                    <livewire:rule-condition-builder :rule-id="$ruleForm->id" :key="'builder-' . $ruleForm->id" />
                </div>
            @endif
        </div>

        <x-slot:footer>
            <x-button :text="__('Cancel')" color="secondary" flat x-on:click="$tsui.close.modal('edit-rule-modal')" />
            <x-button :text="__('Save')" color="primary" wire:click="save" />
        </x-slot:footer>
    </x-modal>
</div>
