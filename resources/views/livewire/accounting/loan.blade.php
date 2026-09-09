<div
    x-data="{
        isEditing: false,
    }"
>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            @section('loan.title')
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-gray-50"
                    >
                        <span x-text="$wire.loan.name"></span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span x-text="$wire.loan.number"></span>
                    </p>
                </div>
            @show
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            <x-button
                color="indigo"
                x-cloak
                x-show="!isEditing"
                class="w-full"
                x-on:click="isEditing = true"
                :text="__('Edit')"
            />
            <x-button
                color="indigo"
                loading="save"
                x-cloak
                x-show="isEditing"
                class="w-full"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success) isEditing = false;
                    })
                "
                :text="__('Save')"
            />
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-cloak
                x-show="isEditing"
                class="w-full"
                x-on:click="
                    isEditing = false;
                    $wire.resetForm();
                "
            />
            @canAction(\FluxErp\Actions\Loan\DeleteLoan::class)
                <x-button
                    color="red"
                    loading="delete"
                    :text="__('Delete')"
                    x-cloak
                    x-show="!isEditing"
                    class="w-full"
                    wire:click="delete()"
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Loan')]) }}"
                />
            @endcanAction
            @canAction(\FluxErp\Actions\Loan\FinanceOrder::class)
                <x-button
                    color="emerald"
                    :text="__('Finance Order')"
                    x-cloak
                    x-show="!isEditing && !$wire.loan.order_id"
                    class="w-full"
                    x-on:click="$tsui.open.modal('finance-order')"
                />
            @endcanAction
            @stack('loan-detail-header-actions')
        </div>
    </div>
    <x-flux::tabs wire:model.live="tab" :$tabs />
    @stack('loan-detail-after-tabs')
    <x-modal id="finance-order" :title="__('Finance Order')">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order')"
                wire:model.number="financeOrder.order_id"
                x-on:select="
                    $wire.changedFinancedOrder($event.detail.select.id)
                "
                select="label:label|value:id"
                required
                :request="[
                    'url' => route('search', \FluxErp\Models\Order::class),
                    'method' => 'POST',
                ]"
            />
            <x-select.styled
                :label="__('Creditor Account')"
                wire:model.number="financeOrder.debit_ledger_account_id"
                select="label:name|value:id|description:number"
                required
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                    'method' => 'POST',
                ]"
            />
            <x-number
                wire:model="financeOrder.amount"
                :label="__('Amount')"
                step="0.01"
                placeholder="0.00"
            />
            <x-date
                wire:model="financeOrder.booking_date"
                :label="__('Booking Date')"
            />
            <x-input
                wire:model="financeOrder.booking_text"
                :label="__('Booking Text')"
            />
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                light
                flat
                x-on:click="$tsui.close.modal('finance-order')"
            />
            <x-button
                :text="__('Save')"
                color="indigo"
                loading="finance"
                x-on:click="
                    $wire.finance().then((success) => {
                        if (success) $tsui.close.modal('finance-order');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
