<div class="flex flex-col gap-6">
    <livewire:contact.accounting.all-discounts
        :headline="__('Total Discounts')"
        wire:model="contact.id"
    />
    <div x-data="{
            get dataTableComponent() { return Alpine.$data($el.querySelector('[tall-datatable]')); },
            showDetail: false,
            detailId: null,
            discounts: [],
            showDetails($event) {
                this.showDetail = false;
                const detailElement = $el.querySelector('#detail');
                if ($event.detail.id === this.detailId) {
                    setTimeout(() => {
                        $el.querySelector('#detail-container').appendChild(detailElement);
                    }, 301);
                    this.detailId = null;

                    return;
                }

                this.detailId = $event.detail.id;
                this.dataTableComponent.$wire.getDiscounts($event.detail.id).then(
                    (response) => {
                            this.discounts = response;
                            setTimeout(() => {
                                let clickedRow = $event.target.closest('tr');
                                let nextSibling = clickedRow.nextElementSibling;
                                $event.target.closest('tbody').insertBefore(detailElement, nextSibling);
                                this.showDetail = true;
                        }, 301);
                    }
                );
            }
        }"
         x-on:data-table-row-clicked="showDetails($event)"
    >
        <table>
            <tbody id="detail-container">
            <tr id="detail" class="border-b border-slate-200">
                <td colspan="100%">
                    <div class="p-4" x-collapse x-cloak x-show="showDetail">
                        <x-card>
                            <x-flux::table>
                                <x-slot:header>
                                    <th class="text-left">
                                        {{ __('Type') }}
                                    </th>
                                    <th class="text-left">
                                        {{ __('Name') }}
                                    </th>
                                    <th class="text-left">
                                        {{ __('Discount') }}
                                    </th>
                                </x-slot:header>
                                <template x-for="discount in discounts">
                                    <x-flux::table.row>
                                        <td>
                                            <div x-text="discount.model_type"></div>
                                        </td>
                                        <td>
                                            <div x-text="discount.model.name"></div>
                                        </td>
                                        <td>
                                            <span x-html="discount.is_percentage ? window.formatters.percentage(discount.discount) : window.formatters.money(discount.discount)" />
                                        </td>
                                    </x-flux::table.row>
                                </template>
                            </x-flux::table>
                        </x-card>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <livewire:contact.accounting.discount-groups
            :headline="__('Discount Groups')"
            wire:model="contact.id"
        />
    </div>
    <div>
        <livewire:contact.accounting.discounts
            wire:model="contact.id"
            :headline="__('Discounts')"
        />
    </div>
    <div>
        <livewire:features.commission-rates :userId="null" :contactId="$this->contact->id"/>
    </div>
</div>
