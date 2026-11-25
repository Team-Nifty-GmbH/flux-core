@use(\Illuminate\Support\Number)

<x-modal
    :id="$rebateAgreementForm->modalName()"
    :title="__('Rebate Agreement')"
    size="xl"
>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <x-input :label="__('Name')" wire:model="rebateAgreementForm.name" />
        <x-select.styled
            :label="__('Order Type')"
            wire:model="rebateAgreementForm.order_type_id"
            required
            select="label:name|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\OrderType::class),
                'method' => 'POST',
                'params' => [
                    'searchFields' => [
                        'name',
                    ],
                    'select' => [
                        'name',
                        'id',
                    ],
                    'where' => [
                        [
                            'order_type_enum',
                            '=',
                            \FluxErp\Enums\OrderTypeEnum::Refund->value,
                        ],
                        [
                            'is_active',
                            '=',
                            true,
                        ],
                    ],
                ],
            ]"
        />
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
                x-on:click="
                    $wire.rebateAgreementForm.tiers.push({
                        from_volume: null,
                        percentage: null,
                    })
                "
            />
        </div>
        <div class="flex flex-col gap-2">
            <template
                x-for="(tier, index) in $wire.rebateAgreementForm.tiers"
                x-bind:key="index"
            >
                <div class="flex items-end gap-2">
                    <x-number
                        class="grow"
                        :label="__('From Volume')"
                        x-model.number="tier.from_volume"
                        step="0.01"
                        min="0"
                    />
                    <x-number
                        class="grow"
                        :label="__('Percentage')"
                        x-model.number="tier.percentage"
                        step="0.01"
                        min="0"
                        max="100"
                    />
                    <x-button
                        icon="trash"
                        color="red"
                        flat
                        x-on:click="
                            $wire.rebateAgreementForm.tiers.splice(index, 1)
                        "
                    />
                </div>
            </template>
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

<x-modal id="settle-rebate-agreement-modal" :title="__('Settle Rebate')">
    <div class="flex flex-col gap-2 text-sm">
        <div class="flex justify-between">
            <span>{{ __('Volume') }}</span>
            <span>
                {{ Number::currency(data_get($calculation, 'volume') ?? 0) }}
            </span>
        </div>
        <div class="flex justify-between">
            <span>{{ __('Percentage') }}</span>
            <span>
                {{ Number::percentage(bcmul(data_get($calculation, 'percentage') ?? 0, 100), maxPrecision: 2) }}
            </span>
        </div>
        <div class="flex justify-between font-semibold">
            <span>{{ __('Total value') }}</span>
            <span>
                {{ Number::currency(data_get($calculation, 'total_net_price') ?? 0) }}
            </span>
        </div>
        @foreach (data_get($calculation, 'positions') ?? [] as $position)
            <div class="flex justify-between pl-4 text-xs">
                <span>
                    {{ Number::percentage(bcmul(data_get($position, 'vat_rate_percentage') ?? 0, 100), maxPrecision: 2) }}
                </span>
                <span>
                    {{ Number::currency(data_get($position, 'total_net_price') ?? 0) }}
                </span>
            </div>
        @endforeach
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            :text="__('Cancel')"
            flat
            x-on:click="$tsui.close.modal('settle-rebate-agreement-modal')"
        />
        <x-button color="indigo" :text="__('Settle')" wire:click="settle()" />
    </x-slot:footer>
</x-modal>
