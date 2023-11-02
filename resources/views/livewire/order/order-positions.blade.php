<div wire:ignore
     x-data="{
            get dataTableComponent() {
                return Alpine.$data($el.querySelector('[tall-datatable]'));
            },
            syncToOrder() {
                orderPositions = this.dataTableComponent.getData();
                $wire.$parent.set('hasUpdatedOrderPositions', true);
            },
            selectedOrderPosition: {},
            livewireSelectedOrderPosition: $wire.entangle('position'),
            selected: $wire.entangle('selected'),
            groups: $wire.entangle('groups'),
            selectedGroupId: 0,
            selectPositions(data) {
                const children = orderPositions.filter(record => record.slug_position?.startsWith(data.record.slug_position)).map(o => o.id);
                if (data.value) {
                    this.selected = [...this.selected, ...children];
                } else {
                    this.selected = this.selected.filter(id => !children.includes(id));
                }
            }
        }"
     x-on:data-table-record-selected="selectPositions($event.detail)"
     x-on:open-modal="selectedOrderPosition = JSON.parse(JSON.stringify($event.detail.record))"
>
    <x-modal.card wire:model="showGroupAdd" title="{{ __('Add to group') }}">
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
    <x-modal.card max-width="6xl" wire:model="showOrderPosition" x-cloak x-transition>
        <div class="relative" x-data="{show: $wire.entangle('showOrderPosition')}" >
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
                                    wire:model.live="productId"
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
                                        'fields' => ['id', 'name', 'product_number'],
                                        'with' => 'media',
                                    ]
                                ]"
                            />
                            <div x-cloak x-show="livewireSelectedOrderPosition.product_id">
                                <x-select
                                        :label="__('Warehouse')"
                                        wire:model.live="position.warehouse_id"
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
                        <x-input
                                :prefix="$order['currency']['symbol']"
                                type="number"
                                :label="__('Unit price :type', ['type' => ($position['is_net'] ?? true) ? __('net') : __('gross')])"
                                x-model="livewireSelectedOrderPosition.unit_price"
                                x-on:change="$el.value = parseNumber($el.value)"
                        >
                        </x-input>
                        <x-input type="number" :label="__('Discount')" x-model="livewireSelectedOrderPosition.discount_percentage"></x-input>
                        <x-select :options="$vatRates" option-label="name" option-value="id" :label="__('Vat rate')" wire:model.live="position.vat_rate_id" />
                    </div>
                </div>
                <x-textarea :label="__('Description')" x-model.lazy="livewireSelectedOrderPosition.description"></x-textarea>
            </div>
            <x-errors />
        </div>
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div x-show="selectedOrderPosition.id">
                    <x-button flat negative :label="__('Delete')" x-on:click="close; $wire.remove(selectedOrderPosition.id).then(() => {syncToOrder();});" />
                </div>
                <div class="flex w-full justify-end">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button
                            primary
                            x-show="!order.is_locked"
                            x-on:click="$wire.save(livewireSelectedOrderPosition, orderPositions).then((data) => {
                                syncToOrder();
                                if(data !== false) {
                                    order = {...order, ...data.order};
                                    $wire.$parent.set('hasUpdatedOrderPositions', true);
                                    close();
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
            <div>
                @include('tall-datatables::livewire.data-table')
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
                                    x-on:click="$wire.remove(selected).then(() => {selected = []; syncToOrder();});"
                                    icon="trash"
                                    negative
                                    :label="__('Delete selected')"
                            />
                            <x-button
                                    x-on:click="$wire.addToGroup().then(() => {syncToOrder();});"
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
