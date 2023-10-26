<div x-data="{
        transaction: {},
        order: {},
        showTransaction(id) {
            $wire.showTransaction(id).then((transaction) => {
                this.transaction = transaction;
                const dataTable = Livewire.find(document.querySelector('#order-list')
                    .querySelector('[wire\\:id]')
                    .getAttribute('wire:id'));
                if (transaction.order_id) {
                    dataTable.set('userFilters', [[{column: 'id', operator: '=', value: transaction.order_id}]]);
                } else {
                    dataTable.set('search', [['transaction_id', transaction.purpose]]);
                }
                $openModal('transaction-details');
            })
        },
        showOrder(id) {
            this.$wire.showOrder(id).then((order) => {
                this.order = order;
                document.querySelector('#order-detail').src = order;
                Alpine.$data(document.querySelector('#order-details').querySelector('[wireui-modal]')).open();
            })
        }
    }"
>
    <x-modal name="transaction-details" max-width="6xl">
        <x-card class="flex flex-col gap-3">
            <div class="placeholder-secondary-400 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 border border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block w-full sm:text-sm rounded-md transition ease-in-out duration-100 focus:outline-none shadow-sm" x-html="window.formatters.date(transaction.booking_date)"></div>
            <div class="placeholder-secondary-400 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 border border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block w-full sm:text-sm rounded-md transition ease-in-out duration-100 focus:outline-none shadow-sm" x-html="window.formatters.date(transaction.value_date)"></div>
            <x-input readonly x-model="transaction.counterpart_name" :label="__('Counterpart Name')"/>
            <x-input readonly x-model="transaction.counterpart_iban" :label="__('Counterpart IBAN')"/>
            <x-input readonly x-model="transaction.counterpart_bank_name" :label="__('Counterpart Bank Name')"/>
            <x-textarea readonly x-model="transaction.purpose" :label="__('Purpose')"/>
            <div class="placeholder-secondary-400 dark:bg-secondary-800 dark:text-secondary-400 dark:placeholder-secondary-500 border border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block w-full sm:text-sm rounded-md transition ease-in-out duration-100 focus:outline-none shadow-sm" x-html="window.formatters.coloredMoney(transaction.amount)"></div>
            <div id="order-list">
                <livewire:data-tables.transactions.order-list />
            </div>
            <x-slot:footer>
                <div class="w-full flex justify-end">
                    <x-button :label="__('Cancel')" x-on:click="close"/>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div id="order-details">
        <x-modal max-width="7xl">
            <x-card class="grid h-screen">
                <embed class="object-contain" height="100%" width="100%" id="order-detail" src="http://localhost/order/1" />
                <x-slot:footer>
                    <div class="w-full flex justify-end">
                        <x-button :label="__('Cancel')" x-on:click="close"/>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    </div>
    <div wire:ignore x-on:data-table-row-clicked="showTransaction($event.detail.id)">
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
