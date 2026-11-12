<x-modal
    id="order-position-configurator"
    :title="__('Configuration')"
    size="4xl"
    persistent
>
    <div
        class="flex flex-col gap-6"
        x-data="{
            amounts: {},
            total: null,
            errors: [],
            money(value) {
                return Number(value ?? 0).toLocaleString(
                    document.documentElement.lang,
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    },
                );
            },
            draft() {
                return Object.entries(this.amounts)
                    .filter(([id, amount]) => Number(amount) > 0)
                    .map(([id, amount]) => ({
                        product_configuration_group_item_id: Number(id),
                        amount: Number(amount),
                    }));
            },
            load() {
                this.total = null;
                this.errors = [];
                this.amounts = Object.fromEntries(
                    ($wire.orderPosition.configuration ?? []).map((entry) => [
                        entry.product_configuration_group_item_id,
                        entry.amount,
                    ]),
                );
                this.preview();
            },
            choose(group, itemId) {
                group.items.forEach((item) => delete this.amounts[item.id]);
                if (itemId) this.amounts[itemId] = 1;
                this.preview();
            },
            save() {
                $wire.orderPosition.configuration = this.draft();
                $wire.addOrderPosition().then((success) => {
                    if (success) {
                        $tsui.close.modal('order-position-configurator');
                        $tsui.close.modal('edit-order-position');
                    }
                });
            },
            preview() {
                $wire.previewConfiguration(this.draft()).then((result) => {
                    this.total = result.total_price ?? null;
                    this.errors = result.errors ?? [];
                });
            },
        }"
        x-on:order-position-configurator-load.window="load()"
        x-on:order-position-configurator-save.window="save()"
    >
        <template
            x-for="group in $wire.configurationGroups"
            x-bind:key="group.id"
        >
            <div class="flex flex-col gap-2">
                <div class="font-semibold">
                    <span x-text="group.name"></span>
                    <span x-cloak x-show="group.is_required">*</span>
                </div>
                <div
                    class="flex items-center gap-2"
                    x-cloak
                    x-show="
                        group.selection_type_enum === 'single' &&
                        !group.is_required
                    "
                >
                    <x-radio
                        x-bind:name="'configurator-group-' + group.id"
                        x-bind:checked="
                            group.items.every((item) => !(amounts[item.id] > 0))
                        "
                        x-on:change="choose(group, null)"
                    />
                    <span>{{ __('No selection') }}</span>
                </div>
                <template x-for="item in group.items" x-bind:key="item.id">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <div
                                x-cloak
                                x-show="group.selection_type_enum === 'single'"
                            >
                                <x-radio
                                    x-bind:id="'configurator-item-' + item.id"
                                    x-bind:name="
                                        'configurator-group-' + group.id
                                    "
                                    x-bind:checked="amounts[item.id] > 0"
                                    x-on:change="choose(group, item.id)"
                                />
                            </div>
                            <div
                                class="w-24"
                                x-cloak
                                x-show="
                                    group.selection_type_enum === 'multiple'
                                "
                            >
                                <x-input
                                    type="number"
                                    min="0"
                                    x-bind:id="'configurator-amount-' + item.id"
                                    x-bind:max="item.max_amount"
                                    x-model.number="amounts[item.id]"
                                    x-on:input.debounce.300ms="preview()"
                                />
                            </div>
                            <span x-text="item.name"></span>
                        </div>
                        <span x-text="money(item.price)"></span>
                    </div>
                </template>
            </div>
        </template>
        <template x-for="error in errors">
            <p class="text-sm text-red-600" x-text="error"></p>
        </template>
        <div class="flex justify-end gap-2 text-lg font-semibold">
            <span>{{ __('Total') }}:</span>
            <span x-text="money(total)"></span>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('order-position-configurator')"
        />
        <x-button
            id="configurator-save"
            color="indigo"
            :text="__('Save')"
            loading="addOrderPosition"
            x-on:click="
                window.dispatchEvent(
                    new CustomEvent('order-position-configurator-save'),
                )
            "
        />
    </x-slot:footer>
</x-modal>
