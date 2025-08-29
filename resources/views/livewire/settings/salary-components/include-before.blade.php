<x-modal :id="$form->modalName()" size="xl">
    <x-slot:title>
        {{ $form->id ? __('Edit Salary Component') : __('Create Salary Component') }}
    </x-slot:title>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-input
            wire:model="form.code"
            :label="__('Code')"
            :error="$errors->first('form.code')"
        />

        <x-input
            wire:model="form.name"
            :label="__('Name')"
            :error="$errors->first('form.name')"
        />

        <x-select.native
            wire:model="form.type"
            :label="__('Type')"
            :error="$errors->first('form.type')"
        >
            <option value="">{{ __('Select Type') }}</option>
            <option value="allowance">{{ __('Allowance') }}</option>
            <option value="benefit">{{ __('Benefit') }}</option>
            <option value="deduction">{{ __('Deduction') }}</option>
            <option value="bonus">{{ __('Bonus') }}</option>
            <option value="commission">{{ __('Commission') }}</option>
        </x-select.native>

        <x-select.native
            wire:model="form.calculation_type"
            :label="__('Calculation Type')"
            :error="$errors->first('form.calculation_type')"
        >
            <option value="">{{ __('Select Calculation Type') }}</option>
            <option value="fixed">{{ __('Fixed Amount') }}</option>
            <option value="percentage">{{ __('Percentage') }}</option>
            <option value="formula">{{ __('Formula') }}</option>
        </x-select.native>

        <div x-show="$wire.form.calculation_type === 'fixed'" x-cloak>
            <x-number
                wire:model="form.default_amount"
                :label="__('Default Amount')"
                :error="$errors->first('form.default_amount')"
                step="0.01"
            />
        </div>

        <div x-show="$wire.form.calculation_type === 'percentage'" x-cloak>
            <x-number
                wire:model="form.default_percentage"
                :label="__('Default Percentage')"
                :error="$errors->first('form.default_percentage')"
                step="0.01"
            />
        </div>

        <div class="col-span-2">
            <x-textarea
                wire:model="form.description"
                :label="__('Description')"
                :error="$errors->first('form.description')"
                rows="2"
            />
        </div>

        <div class="col-span-2" x-show="$wire.form.calculation_type === 'formula'" x-cloak>
            <x-textarea
                wire:model="form.formula"
                :label="__('Formula')"
                :error="$errors->first('form.formula')"
                rows="3"
            />
        </div>

        <x-toggle
            wire:model="form.is_taxable"
            :label="__('Taxable')"
            :error="$errors->first('form.is_taxable')"
        />

        <x-toggle
            wire:model="form.is_social_security_relevant"
            :label="__('Social Security Relevant')"
            :error="$errors->first('form.is_social_security_relevant')"
        />

        <x-toggle
            wire:model="form.is_recurring"
            :label="__('Recurring')"
            :error="$errors->first('form.is_recurring')"
        />

        <x-toggle
            wire:model="form.is_active"
            :label="__('Active')"
            :error="$errors->first('form.is_active')"
        />

        <x-number
            wire:model="form.sort_order"
            :label="__('Sort Order')"
            :error="$errors->first('form.sort_order')"
            step="1"
        />
    </div>

    <x-slot:footer>
        <x-button
            :text="__('Cancel')"
            color="secondary"
            flat
            x-on:click="$modalClose('{{ $form->modalName() }}')"
        />
        <x-button
            :text="__('Save')"
            color="primary"
            wire:click="save"
        />
    </x-slot:footer>
</x-modal>