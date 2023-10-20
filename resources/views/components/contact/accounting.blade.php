<div class="flex flex-col gap-6">
    <livewire:data-tables.contact-all-discounts-list
        :headline="__('Total Discounts')"
        :contact-id="$this->contact['id']"
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
                                <x-table>
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
                                        <x-table.row>
                                            <td>
                                                <div x-text="discount.model_type"></div>
                                            </td>
                                            <td>
                                                <div x-text="discount.model.name"></div>
                                            </td>
                                            <td>
                                               <span x-html="discount.is_percentage ? window.formatters.percentage(discount.discount) : window.formatters.money(discount.discount)" />
                                            </td>
                                        </x-table.row>
                                    </template>
                                </x-table>
                            </x-card>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <livewire:data-tables.discount-group-list
            :headline="__('Discount Groups')"
            :enabled-cols="['name']"
            :filters="[
                'whereRelation' => [
                    'column' => 'contact_discount_group.contact_id',
                    'operator' => '=',
                    'value' => $this->contact['id'],
                    'relation' => 'contacts',
                ],
            ]"
        />
    </div>
    <div>
        <livewire:data-tables.discount-list
            :headline="__('Discounts')"
            :filters="[
                'whereRelation' => [
                    'column' => 'contact_discount.contact_id',
                    'operator' => '=',
                    'value' => $this->contact['id'],
                    'relation' => 'contacts',
                ],
            ]"
        />
    </div>
    <div>
        <div class="flex justify-end">
            <x-select
                x-on:selected="$wire.changeCommissionAgent($event.detail.value); $wire.dispatchTo('features.commission-rates', 'setUserId', [$event.detail.value])"
                :label="__('Commission Agent')"
                wire:model="contact.agent_id"
                option-value="id"
                option-label="label"
                :disabled="! user_can('action.contact.update')"
                :clearable="false"
                :template="[
                    'name'   => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ]
                ]"
            />
        </div>

        <livewire:features.commission-rates :userId="null" :contactId="$this->contact['id']"/>
    </div>
</div>
