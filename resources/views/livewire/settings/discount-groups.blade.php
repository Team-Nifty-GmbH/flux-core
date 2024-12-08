<div x-data="{
    discountGroup: {},
    get dataTable() {
        return Alpine.$data(document.querySelector('[tall-datatable]'));
    },
    saveItem() {
        this.dataTable.$wire.saveItem(this.discountGroup).then((success) => {
            if (success) {
                this.close();
            }
        });
    },
    editItem(recordId) {
        const modal = Alpine.$data(document.getElementById('edit-window').querySelector('[wireui-modal]'));
        if (recordId) {
            this.dataTable.$wire.loadDiscountGroup(recordId).then((discountGroup) => {
                this.discountGroup = discountGroup;
                modal.open();
            });
        } else {
            this.discountGroup = {
                name: '',
                is_active: true,
                discounts: []
            };
            modal.open();
        }
    },
    deleteItem(recordId) {
        window.$wireui.confirmDialog({
                        title: '{{ __('Delete discount group') }}',
                        description: '{{ __('Do you really want to delete this discount group?') }}',
                        icon: 'error',
                        accept: {
                            label: '{{ __('Delete') }}',
                            execute: () => {
                                this.dataTable.$wire.deleteItem(recordId);
                            },
                        },
                        reject: {
                            label: '{{ __('Cancel') }}',
                        }
                    }, $wire.__instance.id);
    }
}">
    <div id="edit-window">
        <x-modal-card :title="__('Manage discount group')">
            <div class="flex flex-col gap-4">
                <x-input x-model="discountGroup.name" label="{{ __('Name') }}" />
                <x-toggle x-model="discountGroup.is_active" label="{{ __('Is Active') }}" />
                <x-table>
                    <x-slot:header>
                        <table.row>
                            <th class="text-left">
                                {{ __('Name') }}
                            </th>
                            <th class="text-left">
                                {{ __('Discount') }}
                            </th>
                        </table.row>
                    </x-slot:header>
                    <template x-for="discount in discountGroup.discounts">
                        <x-table.row>
                            <td>
                                <span x-text="discount.model.name"></span>
                            </td>
                            <td>
                                <span x-text="window.formatters.percentage(discount.discount)"></span>
                            </td>
                        </x-table.row>
                    </template>
                </x-table>
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-4">
                    <x-button flat x-on:click="close()">{{ __('Cancel') }}</x-button>
                    <x-button primary class="mr-2" x-on:click="saveItem()">{{ __('Save') }}</x-button>
                </div>
            </x-slot:footer>
        </x-modal-card>
    </div>
    <livewire:data-tables.settings.discount-group-list />
</div>
