<div
    x-data="{
        selectedOrderPosition: {},
        livewireSelectedOrderPosition: @entangle('position').defer,
        orderPositions: [],
        selectedIndex: null,
        order: $wire.entangle('order').defer,
        selected: [],
        groups: $wire.entangle('groups').defer,
        selectedGroupId: 0,
        selectPositions(data) {
            const children = this.orderPositions.filter(record => record.slug_position?.startsWith(data.record.slug_position)).map(o => o.id);
            if (data.value) {
                this.selected = [...this.selected, ...children];
            } else {
                this.selected = this.selected.filter(id => !children.includes(id));
            }

            $dispatch('update-selected-order-positions', this.selected);
        }
    }"
    x-on:updated-order-positions="orderPositions = $event.detail"
    x-on:data-table-record-selected="selectPositions($event.detail)"
    x-on:open-modal="selectedOrderPosition = JSON.parse(JSON.stringify($event.detail.record)); selectedIndex = $event.detail.index, $wire.edit(selectedOrderPosition)"
>
    <x-modal.card wire:model.defer="showGroupAdd" title="{{  __('Add to group') }}">
        <x-native-select x-model="selectedGroupId">
            <option value="0">{{ __('No group') }}</option>
            <template x-for="group in groups">
                <option x-text="group.name" :value="group.id"></option>
            </template>
        </x-native-select>
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button primary spinner x-on:click="$wire.addToGroup(selectedGroupId)" :label="__('Save')" />
                </div>
            </div>
        </x-slot:footer>
    </x-modal.card>
    <x-modal.card max-width="6xl" wire:model.defer="showOrderPosition" x-cloak x-transition>
        <div class="relative" x-data="{show: $wire.entangle('showOrderPosition').defer}" >
            <x-spinner  wire:target="position"/>
            <div class="space-y-2 p-4" colspan="100%">
                <div class="flex w-full justify-items-stretch gap-3">
                    <div class="flex-auto space-y-2">
                        <x-checkbox x-model="livewireSelectedOrderPosition.is_free_text" :label="__('Comment / Block')" />
                        <x-input :label="__('Name')"  x-model="livewireSelectedOrderPosition.name"/>
                        <div x-cloak x-show="livewireSelectedOrderPosition.is_free_text !== true">
                            <x-select
                                class="pb-4"
                                :disabled="($position['product_id'] ?? false)"
                                :label="__('Product')"
                                wire:model="productId"
                                option-value="id"
                                option-label="label"
                                option-description="product_number"
                                :clearable="false"
                                :template="[
                                    'name'   => 'user-option',
                                ]"
                                :async-data="[
                                    'api' => route('search', \FluxErp\Models\Product::class),
                                    'params' => [
                                        'with' => 'media',
                                    ]
                                ]"
                            />
                            <div x-cloak x-show="livewireSelectedOrderPosition.product_id">
                                <x-select
                                    :label="__('Warehouse')"
                                    wire:model="position.warehouse_id"
                                    option-value="id"
                                    option-label="name"
                                    :async-data="route('search', \FluxErp\Models\Warehouse::class)"
                                />
                            </div>
                            <x-checkbox x-model="livewireSelectedOrderPosition.is_alternative" :label="__('Alternative')" />
                        </div>
                    </div>
                    <div class="flex-auto space-y-2" x-cloak x-show="livewireSelectedOrderPosition.is_free_text !== true">
                        <x-input type="number" min="0" :label="__('Amount')" x-model="livewireSelectedOrderPosition.amount" x-ref="amount"></x-input>
                        <x-input type="number" :label="__('Unit price :type', ['type' => ($position['is_net'] ?? true) ? __('net') : __('gross') ])" x-model="livewireSelectedOrderPosition.unit_price">
                        </x-input>
                        <x-input type="number" :label="__('Discount')" x-model="livewireSelectedOrderPosition.discount_percentage"></x-input>
                        <x-select :options="$vatRates" option-label="name" option-value="id" :label="__('Vat rate')" wire:model="position.vat_rate_id" />
                    </div>
                </div>
                <x-textarea :label="__('Description')" x-model.lazy="livewireSelectedOrderPosition.description"></x-textarea>
            </div>
            <x-errors />
        </div>
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div x-show="selectedOrderPosition.id">
                    <x-button flat negative :label="__('Delete')" x-on:click="close; $wire.remove(selectedOrderPosition.id)" />
                </div>
                <div class="flex w-full justify-end">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button
                        primary
                        x-on:click="
                            $dispatch('order-positions-updated');
                            $wire.save(livewireSelectedOrderPosition, orderPositions).then((data) => {
                              if (data) {
                                  $wire.emitTo('data-tables.order-position-list', selectedIndex !== null ? 'replaceByIndex' : 'addToBottom', data, selectedIndex);
                              }
                          })"
                        :label="__('Save')"
                    />
                </div>
            </div>
        </x-slot:footer>
    </x-modal.card>
    <div class="w-full xl:space-x-6">
        <div class="ml:p-10 relative min-h-full space-y-6">
            <div wire:ignore>
                <livewire:data-tables.order-position-list
                    :order-id="$order['id']"
                    :filters="[['column' => 'order_id', 'operator' => '=', 'value' => $order['id']]]"
                />
                <div x-show="! order.is_locked" class="sticky bottom-6 pt-6">
                    <x-card class="flex gap-4">
                        <x-button
                            :label="__('Add position')"
                            primary
                            icon="plus"
                            x-ref="addPosition"
                            x-on:click="selectedOrderPosition = {}; selectedIndex = null, $wire.edit([]);"
                        />
                        <div
                            x-show="selected.length > 0"
                            x-cloak
                            x-transition
                        >
                            <x-button
                                x-on:click="$wire.remove(selected).then(() => {$dispatch('order-positions-updated'); selected = []});"
                                icon="trash"
                                negative
                                :label="__('Delete selected')"
                            />
                            <x-button
                                x-on:click="$wire.addToGroup();"
                                icon="duplicate"
                                :label="__('Add to group')"
                            />
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</div>
