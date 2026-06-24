@use(Illuminate\Support\Number)

<div>
    <x-card :header="__('Payment Reminder Run')">
        {{-- Bulk actions --}}
        @unless ($this->isEmpty)
            <div class="mb-3 flex flex-wrap justify-end gap-2">
                <x-button
                    :text="__('Send selected')"
                    color="primary"
                    icon="paper-airplane"
                    size="sm"
                    loading="sendSelected"
                    x-on:click="$wire.sendSelected()"
                    x-bind:disabled="!($wire.selectedOrders || []).length"
                />
            </div>
        @endunless

        {{-- Filters --}}
        <div class="flex flex-wrap items-end gap-2">
            <div class="min-w-48 flex-1">
                <x-input
                    wire:model.live.debounce.300ms="search"
                    :placeholder="__('Invoice or customer number')"
                    icon="magnifying-glass"
                    size="sm"
                />
            </div>
            <x-select.styled
                wire:model.live="filterLevel"
                :placeholder="__('All levels')"
                :options="[
                    ['label' => __('Reminder Level') . ' 1', 'value' => '1'],
                    ['label' => __('Reminder Level') . ' 2', 'value' => '2'],
                    ['label' => __('Reminder Level') . ' 3', 'value' => '3'],
                ]"
                select="label:label|value:value"
                size="sm"
                class="w-44"
            />
            <x-number
                wire:model.live.debounce.300ms="minOverdueDays"
                :placeholder="__('Min. days overdue')"
                min="0"
                size="sm"
                class="w-40"
            />
            <x-select.styled
                wire:model.live="sort"
                required
                :options="[
                    ['label' => __('Days overdue (desc)'), 'value' => 'overdue_days_desc'],
                    ['label' => __('Days overdue (asc)'), 'value' => 'overdue_days_asc'],
                    ['label' => __('Balance (desc)'), 'value' => 'balance_desc'],
                    ['label' => __('Balance (asc)'), 'value' => 'balance_asc'],
                    ['label' => __('Customer'), 'value' => 'contact_asc'],
                ]"
                select="label:label|value:value"
                size="sm"
                class="w-44"
            />
        </div>

        @if ($this->isEmpty)
            <div class="text-secondary-500 py-12 text-center text-sm">
                {{ __('No payment reminders are due.') }}
            </div>
        @else
            @php
                $totalBalance = collect($groups)->sum(fn ($g) => (float) $g['total_balance']);
            @endphp
            <div class="text-secondary-500 mt-4 mb-2 text-xs">
                {{ count($groups) }} {{ __('Groups') }} &middot; {{ collect($groups)->sum('order_count') }} {{ __('invoice(s)') }} &middot; {{ __('Total') }}: {{ Number::currency($totalBalance, locale: app()->getLocale()) }}
            </div>
            <div
                class="border-secondary-100 dark:border-secondary-700 divide-secondary-100 dark:divide-secondary-700 divide-y overflow-hidden rounded-lg border"
            >
                @foreach ($groups as $group)
                    @php
                        $groupOrderIds = array_column($group['orders'], 'id');
                        $firstOrderId = $group['orders'][0]['id'] ?? null;
                        $levelColor = match ((int) $group['next_level']) {
                            1 => 'amber',
                            2 => 'orange',
                            default => 'red',
                        };
                    @endphp
                    <div
                        wire:key="group-{{ $group['key'] }}"
                        x-data="{ open: false }"
                    >
                        {{-- Compact group row --}}
                        <div
                            class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 flex cursor-pointer items-center gap-3 px-3 py-2"
                            x-on:click="open = !open"
                        >
                            <div x-on:click.stop>
                                <x-checkbox
                                    x-bind:checked="[{{ implode(',', $groupOrderIds) }}].map(String).every(id => ($wire.selectedOrders || []).includes(id))"
                                    x-on:click.stop.prevent="$wire.toggleGroup('{{ $group['key'] }}')"
                                    :title="__('Select all')"
                                />
                            </div>
                            <x-icon
                                name="chevron-right"
                                class="text-secondary-400 h-4 w-4 shrink-0 transition-transform"
                                x-bind:class="open && 'rotate-90'"
                            />
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <span class="truncate text-sm font-medium">
                                    {{ $group['contact_name'] ?? __('Unknown') }}
                                </span>
                                <span
                                    class="text-secondary-500 flex flex-wrap items-center gap-x-1.5 text-sm"
                                >
                                    <x-badge
                                        :color="$levelColor"
                                        :text="__('Reminder Level') . ' ' . $group['next_level']"
                                        size="sm"
                                    />
                                    <span>
                                        <span
                                            class="text-secondary-700 dark:text-secondary-200 font-bold"
                                            >{{ $group['order_count'] }}</span
                                        >
                                        {{ __('invoice(s)') }}
                                    </span>
                                    <span>&middot;</span>
                                    <span
                                        >{{ $group['max_overdue_days'] }} {{ __('days overdue') }}</span
                                    >
                                </span>
                            </div>
                            <span
                                class="text-sm font-semibold whitespace-nowrap"
                            >
                                {{ Number::currency((float) $group['total_balance'], locale: app()->getLocale()) }}
                            </span>
                            <div
                                class="flex items-center gap-1"
                                x-on:click.stop
                            >
                                @if ($firstOrderId)
                                    <x-button
                                        icon="eye"
                                        color="secondary"
                                        size="sm"
                                        flat
                                        :title="__('Preview')"
                                        loading="preview({{ $firstOrderId }})"
                                        wire:click="preview({{ $firstOrderId }})"
                                    />
                                @endif
                                <x-button
                                    :text="__('Send')"
                                    color="primary"
                                    icon="paper-airplane"
                                    size="sm"
                                    loading="sendGroup('{{ $group['key'] }}')"
                                    wire:click="sendGroup('{{ $group['key'] }}')"
                                />
                            </div>
                        </div>

                        {{-- Expanded detail --}}
                        <div
                            x-show="open"
                            x-cloak
                            x-collapse
                            class="bg-secondary-50/50 dark:bg-secondary-800/30 px-3 pt-1 pb-3"
                        >
                            <div class="max-w-md py-2">
                                <x-input
                                    wire:model="recipientEmails.{{ $group['key'] }}"
                                    :label="__('Recipient')"
                                    type="email"
                                    icon="envelope"
                                    :placeholder="__('No email')"
                                    size="sm"
                                />
                            </div>
                            <div class="flex flex-col">
                                @foreach ($group['orders'] as $order)
                                    <div
                                        class="border-secondary-100 dark:border-secondary-700/50 flex items-center gap-3 border-t py-1.5 text-sm"
                                    >
                                        <div x-on:click.stop>
                                            <x-checkbox
                                                wire:model="selectedOrders"
                                                value="{{ $order['id'] }}"
                                            />
                                        </div>
                                        <span
                                            class="flex-1 font-medium"
                                            >{{ $order['invoice_number'] }}</span
                                        >
                                        <span
                                            class="text-secondary-500 whitespace-nowrap"
                                        >
                                            {{ $order['next_date'] }} &middot; {{ $order['overdue_days'] }} {{ __('days') }}
                                        </span>
                                        <span
                                            class="w-28 text-right font-medium"
                                        >
                                            {{ Number::currency((float) $order['balance'], locale: app()->getLocale()) }}
                                        </span>
                                        <x-button
                                            icon="eye"
                                            color="secondary"
                                            size="sm"
                                            flat
                                            :title="__('Preview')"
                                            loading="preview({{ $order['id'] }})"
                                            wire:click="preview({{ $order['id'] }})"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <x-modal
        wire="showPreview"
        size="full"
        :title="__('Payment Reminder Preview')"
    >
        @if ($previewSrc)
            <iframe
                src="{{ $previewSrc }}"
                class="h-[85vh] w-full rounded-lg border-0"
            ></iframe>
        @endif
    </x-modal>
</div>
