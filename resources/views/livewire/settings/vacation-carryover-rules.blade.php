<div>
    <x-modal
        :id="$vacationCarryoverRuleForm->modalName()"
        :title="__('Vacation Carryover Rule')"
    >
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

            <div class="grid grid-cols-2 gap-4">
                <x-number
                    wire:model="vacationCarryoverRuleForm.expires_at_day"
                    min="1"
                    max="31"
                    :label="__('Expires At Day')"
                />
                <x-number
                    wire:model="vacationCarryoverRuleForm.expires_at_month"
                    min="1"
                    max="12"
                    :label="__('Expires At Month')"
                />
            </div>

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
                x-on:click="$tsui.close.modal('{{ $vacationCarryoverRuleForm->modalName() }}')"
            />
            <x-button
                color="primary"
                :text="__('Save')"
                x-on:click="$wire.save().then((success) => { if(success) $tsui.close.modal('{{ $vacationCarryoverRuleForm->modalName() }}') })"
            />
        </x-slot:footer>
    </x-modal>
</div>
