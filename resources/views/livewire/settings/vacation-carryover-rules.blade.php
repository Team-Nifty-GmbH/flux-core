<div>
    <x-modal :id="$vacationCarryoverRuleForm->modalName()" :title="__('Vacation Carryover Rule')">
    <div class="flex flex-col gap-4">
        <x-input
            wire:model="vacationCarryoverRuleForm.name"
            :label="__('Name')"
            required
        />

        <x-number
            wire:model="vacationCarryoverRuleForm.max_days"
            min="0"
            max="365"
            :label="__('Maximum Carryover Days')"
            :hint="__('Maximum vacation days that can be carried over to next year')"
        />

        <x-number
            wire:model="vacationCarryoverRuleForm.expires_after_months"
            min="0"
            max="24"
            :label="__('Expires After Months')"
            :hint="__('Number of months after which carried over days expire')"
        />

        <x-toggle
            wire:model="vacationCarryoverRuleForm.is_active"
            :label="__('Is Active')"
        />
        <x-toggle
            wire:model="vacationCarryoverRuleForm.is_default"
            :label="__('Is Default')"
        />
    </div>

    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('{{ $vacationCarryoverRuleForm->modalName() }}')"
        />
        <x-button
            color="primary"
            :text="__('Save')"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $vacationCarryoverRuleForm->modalName() }}') })"
        />
    </x-slot>
    </x-modal>
</div>
