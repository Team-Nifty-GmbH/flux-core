<x-card :header="__('Contract')" class="flex flex-col gap-1.5">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Repeat')"
            wire:model="schedule.cron.methods.basic"
            select="label:label|value:value"
            x-on:select="
                $wire.schedule.cron.parameters.basic =
                    $event.detail.select.value === 'yearlyOn'
                        ? [1, 1, '00:00']
                        : [1, '00:00'];
                $wire.previewSchedule();
            "
            :options="[
                ['value' => 'monthlyOn', 'label' => __('Monthly')],
                ['value' => 'quarterlyOn', 'label' => __('Quarterly')],
                ['value' => 'yearlyOn', 'label' => __('Yearly')],
                ['value' => 'weeklyOn', 'label' => __('Weekly')],
            ]"
        />
        <x-date
            wire:model.live="schedule.due_at"
            :label="__('Next Execution')"
            timezone="UTC"
        />
        <x-label :label="__('End')" />
        <x-radio
            id="contract-end-never"
            name="contract-end"
            :label="__('Never')"
            value=""
            wire:model.live="schedule.end_radio"
        />
        <x-radio
            id="contract-end-date"
            name="contract-end"
            :label="__('Ends At')"
            value="ends_at"
            wire:model.live="schedule.end_radio"
        />
        <div
            x-cloak
            x-show="$wire.schedule.end_radio === 'ends_at'"
        >
            <x-date
                wire:model="schedule.ends_at"
                timezone="UTC"
            />
        </div>
        <div class="flex items-end gap-1.5">
            <x-number
                :label="__('Contract Total Amount')"
                wire:model="order.contract_total_amount"
                step="0.01"
            />
            <x-button
                color="secondary"
                light
                :text="__('Calculate')"
                :title="__('Rate multiplied by the remaining cycles until the end date')"
                wire:click="prefillContractTotal()"
            />
        </div>
        @if (! is_null($order->balance) && ! is_null($order->contract_total_amount))
            <x-alert color="indigo" :title="__('Remaining') . ': ' . \Illuminate\Support\Number::currency(
                (float) $order->balance,
                $order->currency?->iso ?? '',
                app()->getLocale()
            )" />
        @endif
        <div class="flex items-center justify-between">
            <x-button
                color="secondary"
                light
                flat
                :text="__('Advanced')"
                x-on:click="$tsui.open.modal('edit-schedule')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="
                    $wire.saveSchedule().then((success) => {
                        if (success) $wire.save();
                    })
                "
            />
        </div>
    </div>
</x-card>
