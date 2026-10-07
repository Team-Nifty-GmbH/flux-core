@extends('flux::livewire.order.purchase')
@section('modals')
    @parent
    @use(FluxErp\Enums\OrderTypeEnum)
    <x-modal id="edit-schedule" :title="__('Edit Schedule')">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order type')"
                wire:model="schedule.parameters.orderTypeId"
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
                                'is_active',
                                '=',
                                true,
                            ],
                            [
                                'is_hidden',
                                '=',
                                false,
                            ],
                        ],
                        'whereIn' => [
                            [
                                'order_type_enum',
                                collect(OrderTypeEnum::cases())
                                    ->filter(fn(OrderTypeEnum $type) => $type->isPurchase() && ! $type->isSubscription())
                                    ->toArray(),
                            ],
                        ],
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Repeat')"
                autocomplete="off"
                required
                searchable
                wire:model.live="schedule.cron.methods.basic"
                x-on:select="$wire.previewSchedule()"
                :options="$frequencies"
            />
            <x-flux::schedule.basic-parameters
                :method="data_get($this->schedule, 'cron.methods.basic')"
                preview
            />
            <x-date
                wire:model.live="schedule.due_at"
                :label="__('Next Execution')"
                timezone="UTC"
            />
            <div
                x-cloak
                x-show="
                    $wire.schedule.due_at &&
                    new Date($wire.schedule.due_at) <= new Date()
                "
            >
                <x-alert color="amber">
                    {{ __('The schedule will be executed immediately on the next run.') }}
                </x-alert>
            </div>
            <x-toggle
                wire:model="schedule.is_active"
                :label="__('Is Active')"
            />
            <x-toggle
                wire:model="order.is_self_billed"
                :label="__('Supplier sends no invoice')"
                :hint="__('The recurring order is the document itself and gets its own invoice number, for rent, insurance or fees. Leave it off where the supplier sends an invoice that is taken over onto the order.')"
            />
            <div
                x-cloak
                x-show="$wire.schedule.nextExecutionDates.length > 0"
                class="border-t pt-4"
            >
                <x-label :label="__('Preview next executions')" />
                <ul class="mt-1 space-y-1">
                    <template
                        x-for="entry in $wire.schedule.nextExecutionDates"
                        x-bind:key="entry.date"
                    >
                        <li
                            class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400"
                        >
                            <x-icon
                                name="chevron-right"
                                class="mt-1 h-3 w-3 shrink-0"
                            />
                            <span class="flex flex-col">
                                <span
                                    x-text="
                                        new Date(entry.date + 'Z').toLocaleString('{{ app()->getLocale() }}', {
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    "
                                ></span>
                                <span
                                    x-cloak
                                    x-show="entry.system_delivery_date"
                                    class="text-xs text-gray-400 dark:text-gray-500"
                                    x-text="
                                        '{{ __('Performance period') }}: ' +
                                        new Date(entry.system_delivery_date + 'T00:00:00').toLocaleDateString('{{ app()->getLocale() }}') +
                                        ' – ' +
                                        new Date(entry.system_delivery_date_end + 'T00:00:00').toLocaleDateString('{{ app()->getLocale() }}')
                                    "
                                ></span>
                            </span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$tsui.close.modal('edit-schedule')"
                :text="__('Cancel')"
            />
            <x-button
                color="indigo"
                x-on:click="
                    $wire.saveSchedule().then((success) => {
                        if (success) $tsui.close.modal('edit-schedule');
                    })
                "
                primary
                :text="__('Save')"
            />
        </x-slot:footer>
    </x-modal>
@endsection

@section('actions')
    @parent
    <x-flux::order.contract-card :order="$order" />
@endsection
