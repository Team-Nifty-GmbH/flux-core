<x-modal id="edit-order-type-modal">
    <div class="space-y-8 divide-y divide-gray-200">
        <div>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                <div class="space-y-3 sm:col-span-6">
                    <x-input
                        wire:model="orderType.name"
                        :label="__('Order Type Name')"
                    />
                    <x-textarea
                        wire:model="orderType.description"
                        :label="__('Description')"
                    />
                    <x-select.styled
                        :label="__('Client')"
                        :placeholder="__('Select a Client')"
                        wire:model="orderType.client_id"
                        select="label:name|value:id"
                        :request="route('search', \FluxErp\Models\Client::class)"
                    />
                    <x-select.styled
                        :label="__('Order Type')"
                        :disabled="(bool) $orderType->id"
                        :placeholder="__('Select Order Type')"
                        wire:model="orderType.order_type_enum"
                        :options="\FluxErp\Enums\OrderTypeEnum::valuesLocalized()"
                    />
                    <x-select.styled
                        :label="__('Print Layouts')"
                        :placeholder="__('Select Print Layouts')"
                        wire:model="orderType.print_layouts"
                        multiple
                        :options="$printViews"
                    />
                    <x-select.styled
                        :label="__('Post stock')"
                        :hint="__('Stock will be posted on creation of given documents')"
                        wire:model="orderType.post_stock_print_layouts"
                        multiple
                        :options="$printViews"
                    />
                    <x-select.styled
                        :label="__('Reserve stock')"
                        :hint="__('Stock will be reserved on creation of given documents.') . ' ' . __('Stock posting has priority over stock reservation.')"
                        wire:model="orderType.reserve_stock_print_layouts"
                        multiple
                        :options="$printViews"
                    />
                    <x-checkbox
                        wire:model="orderType.is_active"
                        :label="__('Is Active')"
                    />
                    <x-checkbox
                        wire:model="orderType.is_hidden"
                        :label="__('Is Hidden')"
                    />
                    <x-checkbox
                        wire:model="orderType.is_visible_in_sidebar"
                        :label="__('Is Visible In Sidebar')"
                    />
                    <x-select.styled
                        :label="__('Email Template')"
                        wire:model="orderType.email_template_id"
                        select="label:label|value:id"
                        :request="[
                            'url' => route('search', \FluxErp\Models\EmailTemplate::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => [
                                    'name',
                                ],
                                'where' => [
                                    [
                                        'model_type',
                                        '=',
                                        morph_alias(\FluxErp\Models\Order::class),
                                    ],
                                ],
                            ],
                        ]"
                    />
                </div>
            </div>
        </div>
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$modalClose('edit-order-type-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            wire:click="save().then((success) => {if (success) $modalClose('edit-order-type-modal');})"
        />
    </x-slot>
</x-modal>
