<x-card class="w-full">
    <div
        class="dark:divide-secondary-700 dark:border-secondary-700 mb-6 grid grid-cols-1 divide-y divide-gray-200 rounded-lg border border-gray-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0"
    >
        <div class="px-4 py-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Allowance') }} {{ $this->allowance['year'] }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                @if (! $this->allowance['is_allowed'])
                    {{ __('Not allowed') }}
                @elseif ($this->allowance['is_capped'])
                    {{ $this->allowance['allowance'] }}
                @else
                    {{ __('Unlimited') }}
                @endif
            </div>
        </div>
        <div class="px-4 py-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Used') }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                {{ $this->allowance['used'] }}
            </div>
        </div>
        <div class="bg-gray-50/70 px-4 py-3 dark:bg-white/5">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Remaining') }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                {{ $this->allowance['remaining'] ?? __('Unlimited') }}
            </div>
        </div>
    </div>

    <x-table
        :headers="$this->extraRepaymentHeaders"
        :rows="$this->extraRepayments"
        striped
    />

    <x-slot:footer>
        <x-button
            color="indigo"
            :text="__('New Extra Repayment')"
            :disabled="! $this->allowance['is_allowed']"
            x-on:click="$tsui.open.modal('create-extra-repayment-modal')"
        />
    </x-slot:footer>
</x-card>

<x-modal
    id="create-extra-repayment-modal"
    :title="__('New Extra Repayment')"
    size="lg"
>
    <div class="flex flex-col gap-1.5">
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-number
                wire:model.live.debounce.500ms="extraRepayment.amount"
                :label="__('Amount')"
                step="0.01"
                min="0"
                placeholder="0.00"
                required
            />
            <x-date
                wire:model="extraRepayment.executed_at"
                :label="__('Executed At')"
                required
            />
        </div>
        <x-select.styled
            wire:model.live="extraRepayment.schedule_adjustment_type_enum"
            :label="__('Schedule Adjustment')"
            select="label:label|value:value"
            required
            :options="\FluxErp\Enums\ScheduleAdjustmentTypeEnum::valuesLocalized()"
        />
        <x-input wire:model="extraRepayment.note" :label="__('Note')" />

        @if ($this->extraRepaymentPreview)
            <div
                class="dark:border-secondary-700 mt-1.5 rounded-lg border border-gray-200 px-4 py-3 text-sm"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Effect on the remaining schedule') }}
                </div>
                <div class="mt-1.5 grid grid-cols-2 gap-1.5 md:grid-cols-4">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Interest Saved') }}
                        </div>
                        <div class="font-semibold tabular-nums">
                            {{ $this->extraRepaymentPreview['interest_saved'] }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Installments Saved') }}
                        </div>
                        <div class="font-semibold tabular-nums">
                            {{ $this->extraRepaymentPreview['installments_saved'] }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Remaining') }}
                        </div>
                        <div class="font-semibold tabular-nums">
                            {{ $this->extraRepaymentPreview['remaining'] }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Ends At') }}
                        </div>
                        <div class="font-semibold tabular-nums">
                            {{ $this->extraRepaymentPreview['ends_at'] }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('create-extra-repayment-modal')"
        />
        <x-button
            color="indigo"
            loading="saveExtraRepayment"
            :text="__('Save')"
            x-on:click="
                $wire.saveExtraRepayment().then((saved) => {
                    if (saved) {
                        $tsui.close.modal('create-extra-repayment-modal');
                    }
                })
            "
        />
    </x-slot:footer>
</x-modal>
