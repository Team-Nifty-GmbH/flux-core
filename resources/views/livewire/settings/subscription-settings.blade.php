@use(FluxErp\Facades\Editor)
@use(FluxErp\Models\Order)

<div class="flex flex-col gap-4">
    <div>
        <x-label :label="__('Default Minimum Contract Duration')" />
        <div class="grid grid-cols-2 gap-4">
            <x-number
                wire:model="subscriptionSettingsForm.default_minimum_duration_value"
                :label="__('Value')"
                :min="0"
            />
            <x-select.styled
                wire:model="subscriptionSettingsForm.default_minimum_duration_unit"
                :label="__('Unit')"
                select="label:label|value:value"
                :options="[
                    ['value' => 'days', 'label' => __('Days')],
                    ['value' => 'weeks', 'label' => __('Weeks')],
                    ['value' => 'months', 'label' => __('Months')],
                    ['value' => 'years', 'label' => __('Years')],
                ]"
            />
        </div>
    </div>

    <div>
        <x-label :label="__('Default Cancellation Notice Period')" />
        <div class="grid grid-cols-2 gap-4">
            <x-number
                wire:model="subscriptionSettingsForm.default_cancellation_notice_value"
                :label="__('Value')"
                :min="0"
            />
            <x-select.styled
                wire:model="subscriptionSettingsForm.default_cancellation_notice_unit"
                :label="__('Unit')"
                select="label:label|value:value"
                :options="[
                    ['value' => 'days', 'label' => __('Days')],
                    ['value' => 'weeks', 'label' => __('Weeks')],
                    ['value' => 'months', 'label' => __('Months')],
                    ['value' => 'years', 'label' => __('Years')],
                ]"
            />
        </div>
    </div>

    <div>
        <x-label :label="__('Cancellation Text')" />
        <x-flux::editor
            wire:model="subscriptionSettingsForm.cancellation_text"
            :blade-variables="Editor::getTranslatedVariables(Order::class, 'subscription')"
        />
    </div>

    <div class="flex justify-end">
        <x-button wire:click="save" :text="__('Save')" />
    </div>
</div>
