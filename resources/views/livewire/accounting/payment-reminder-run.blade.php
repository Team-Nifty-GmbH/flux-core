@use(Illuminate\Support\Number)

<div>
    <x-card :header="__('Payment Reminder Run')">
        <x-slot:action>
            <x-button
                :text="__('Send all')"
                color="primary"
                icon="paper-airplane"
                x-on:click="$wire.sendAll()"
                x-bind:disabled="$wire.isEmpty"
            />
            <x-button
                :text="__('Send selected')"
                color="indigo"
                icon="paper-airplane"
                x-on:click="$wire.sendSelected()"
                x-bind:disabled="!($wire.selected || []).length"
            />
        </x-slot:action>

        @if ($this->isEmpty)
            <div class="text-secondary-500 py-12 text-center text-sm">
                {{ __('No payment reminders are due today.') }}
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($groups as $group)
                    <x-card>
                        <div class="flex items-start justify-between gap-3">
                            <label
                                class="flex flex-1 cursor-pointer items-start gap-3"
                            >
                                <x-checkbox
                                    wire:model.live="selected"
                                    value="{{ $group['key'] }}"
                                />
                                <div class="flex flex-col gap-1">
                                    <div class="font-medium">
                                        {{ $group['contact_name'] ?? __('Unknown') }}
                                    </div>
                                    <div class="text-secondary-500 text-sm">
                                        {{ __('Reminder Level') }}: {{ $group['next_level'] }} &middot; {{ $group['order_count'] }} {{ __('invoice(s)') }} &middot; {{ $group['recipient_email'] ?? __('No email') }}
                                    </div>
                                </div>
                            </label>
                            <div class="flex items-center gap-2">
                                <div class="text-right text-sm font-medium">
                                    {{ Number::currency((float) $group['total_balance'], locale: app()->getLocale()) }}
                                </div>
                                <x-button
                                    :text="__('Send')"
                                    color="primary"
                                    icon="paper-airplane"
                                    size="sm"
                                    wire:click="sendGroup('{{ $group['key'] }}')"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex flex-col gap-1 text-sm">
                            @foreach ($group['orders'] as $order)
                                <div
                                    class="border-secondary-100 dark:border-secondary-700 flex justify-between border-t py-1"
                                >
                                    <span>{{ $order['invoice_number'] }}</span>
                                    <span class="text-secondary-500">
                                        {{ __('Due') }}: {{ $order['next_date'] }}
                                    </span>
                                    <span class="font-medium">
                                        {{ Number::currency((float) $order['balance'], locale: app()->getLocale()) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </x-card>
</div>
