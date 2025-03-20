<div
    x-data="{
        discountGroup: {},
        get dataTable() {
            return Alpine.$data(document.querySelector('[tall-datatable]'))
        },
        saveItem() {
            this.dataTable.$wire.saveItem(this.discountGroup).then((success) => {
                if (success) {
                    $modalClose('manage-discount-group-modal')
                }
            })
        },
        editItem(recordId) {
            if (recordId) {
                this.dataTable.$wire
                    .loadDiscountGroup(recordId)
                    .then((discountGroup) => {
                        this.discountGroup = discountGroup
                    })
            } else {
                this.discountGroup = {
                    name: '',
                    is_active: true,
                    discounts: [],
                }
            }

            $modalOpen('manage-discount-group-modal')
        },
        deleteItem(recordId) {
            $interaction()
                .wireable()
                .error(
                    '{{ __('Delete discount group') }}',
                    '{{ __('Do you really want to delete this discount group?') }}',
                )
                .confirm('{{ __('Delete') }}', () => {
                    this.dataTable.$wire.deleteItem(recordId)
                })
                .cancel('{{ __('Cancel') }}')
                .send()
        },
    }"
>
    <x-modal
        id="manage-discount-group-modal"
        :title="__('Manage discount group')"
    >
        <div class="flex flex-col gap-4">
            <x-input x-model="discountGroup.name" label="{{ __('Name') }}" />
            <x-toggle
                x-model="discountGroup.is_active"
                label="{{ __('Is Active') }}"
            />
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
                </x-slot>
                <template x-for="discount in discountGroup.discounts">
                    <x-table.row>
                        <td>
                            <span x-text="discount.model.name"></span>
                        </td>
                        <td>
                            <span
                                x-text="window.formatters.percentage(discount.discount)"
                            ></span>
                        </td>
                    </x-table.row>
                </template>
            </x-table>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                x-on:click="$modalClose('manage-discount-group-modal')"
            >
                {{ __('Cancel') }}
            </x-button>
            <x-button color="indigo" class="mr-2" x-on:click="saveItem()">
                {{ __('Save') }}
            </x-button>
        </x-slot>
    </x-modal>
    <livewire:data-tables.settings.discount-group-list />
</div>
