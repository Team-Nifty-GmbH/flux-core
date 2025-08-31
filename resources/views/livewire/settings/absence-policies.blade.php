<div>
    <x-modal :id="$absencePolicyForm->modalName()">
        <div class="space-y-4">
            <x-input
                wire:model="absencePolicyForm.name"
                :label="__('Name')"
                required
            />

            <x-input
                wire:model="absencePolicyForm.max_consecutive_days"
                type="number"
                min="1"
                :label="__('Max Consecutive Days')"
            />

            <x-input
                wire:model="absencePolicyForm.min_notice_days"
                type="number"
                min="0"
                :label="__('Min Notice Days')"
            />

            <x-toggle
                wire:model="absencePolicyForm.requires_substitute"
                :label="__('Requires Substitute')"
            />

            <x-toggle
                wire:model="absencePolicyForm.requires_documentation"
                :label="__('Requires Documentation')"
            />

            <div x-show="$wire.absencePolicyForm.requires_documentation" x-cloak>
                <x-input
                    wire:model="absencePolicyForm.documentation_after_days"
                    type="number"
                    min="1"
                    :label="__('Documentation After Days')"
                />
            </div>

            <x-toggle
                wire:model="absencePolicyForm.is_active"
                :label="__('Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $absencePolicyForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $absencePolicyForm->modalName() }}') })"
            />
        </x-slot:footer>
    </x-modal>
</div>
