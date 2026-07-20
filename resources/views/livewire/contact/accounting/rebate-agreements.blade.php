@use(\Illuminate\Support\Number)

<x-modal
    :id="$rebateAgreementForm->modalName()"
    :title="__('Rebate Agreement')"
    size="xl"
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-input :label="__('Name')" wire:model="rebateAgreementForm.name" />
        <div class="flex items-end">
            <x-toggle
                :label="__('Is Active')"
                wire:model="rebateAgreementForm.is_active"
            />
        </div>
        <x-date
            :label="__('Period Start')"
            wire:model="rebateAgreementForm.period_start"
        />
        <x-date
            :label="__('Period End')"
            wire:model="rebateAgreementForm.period_end"
        />
    </div>
    <div class="pt-4">
        <div class="flex items-center justify-between pb-2">
            <div class="text-sm font-medium">{{ __('Tiers') }}</div>
            <x-button
                :text="__('Add Tier')"
                icon="plus"
                color="indigo"
                flat
                wire:click="addTier()"
            />
        </div>
        <div class="flex flex-col gap-2">
            @foreach ($rebateAgreementForm->tiers as $index => $tier)
                <div class="flex items-end gap-2">
                    <x-number
                        class="grow"
                        :label="__('From Volume')"
                        wire:model="rebateAgreementForm.tiers.{{ $index }}.from_volume"
                        step="0.01"
                        min="0"
                    />
                    <x-number
                        class="grow"
                        :label="__('Percentage')"
                        wire:model="rebateAgreementForm.tiers.{{ $index }}.percentage"
                        step="0.01"
                        min="0"
                        max="100"
                    />
                    <x-button
                        icon="trash"
                        color="red"
                        flat
                        wire:click="removeTier({{ $index }})"
                    />
                </div>
            @endforeach
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            :text="__('Cancel')"
            flat
            x-on:click="$tsui.close.modal('{{ $rebateAgreementForm->modalName() }}')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('{{ $rebateAgreementForm->modalName() }}');
                })
            "
        />
    </x-slot:footer>
</x-modal>

<x-modal id="settle-rebate-agreement-modal" :title="__('Calculate Rebate')">
    <div class="flex flex-col gap-2 text-sm">
        <div class="flex justify-between">
            <span>{{ __('Volume') }}</span>
            <span
                >{{ Number::currency((float) data_get($calculation, 'volume', 0)) }}</span
            >
        </div>
        <div class="flex justify-between">
            <span>{{ __('Percentage') }}</span>
            <span>
                {{ data_get($calculation, 'percentage')
                    ? Number::percentage(bcmul(data_get($calculation, 'percentage'), 100), maxPrecision: 2)
                    : '-' }}
            </span>
        </div>
        <div class="flex justify-between font-semibold">
            <span>{{ __('Total value') }}</span>
            <span
                >{{ Number::currency((float) data_get($calculation, 'total_net_price', 0)) }}</span
            >
        </div>
        @foreach (data_get($calculation, 'positions') ?? [] as $position)
            <div class="flex justify-between pl-4 text-xs">
                <span>
                    {{ Number::percentage(bcmul(data_get($position, 'vat_rate_percentage') ?? 0, 100), maxPrecision: 2) }}
                </span>
                <span>
                    {{ Number::currency((float) data_get($position, 'total_net_price')) }}
                </span>
            </div>
        @endforeach
    </div>
    @if (! data_get($calculation, 'positions'))
        <div class="pt-4 text-sm">
            {{ __('The volume does not reach any tier of this rebate agreement.') }}
        </div>
    @endif
    <x-slot:footer>
        <x-button
            color="secondary"
            :text="__('Cancel')"
            flat
            x-on:click="$tsui.close.modal('settle-rebate-agreement-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Create Refund')"
            x-bind:disabled="
                !$wire.orderTypeId || !$wire.calculation?.positions?.length
            "
            wire:click="settle()"
        />
    </x-slot:footer>
</x-modal>
