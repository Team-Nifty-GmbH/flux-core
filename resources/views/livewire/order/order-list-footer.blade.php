@section('order-positions-footer-card')
<div
    wire:ignore
    x-show="! $wire.order.is_locked"
    x-cloak
    class="sticky bottom-6 pt-6"
>
    <x-card>
        <form
            class="flex flex-col gap-4"
            x-on:submit.prevent="
                $wire
                    .quickAdd()
                    .then(() => (Alpine.$data($el.querySelector('[x-data]')).show = true))
            "
        >
            <div class="flex flex-col gap-4">
                @section('order-positions-footer-card.inputs')
                <x-select.styled
                    class="pb-4"
                    :label="__('Product')"
                    x-on:select="$wire.changedProductId($event.detail.select.id).then(() => {
                        const input = $refs.quickAddAmount.querySelector('input');
                        input.focus();
                        input.select();
                    })"
                    wire:model="orderPosition.product_id"
                    required
                    select="label:label|value:id|description:product_number"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Product::class),
                        'method' => 'POST',
                        'params' => [
                            'whereDoesntHave' => 'children',
                            'fields' => [
                                'id',
                                'name',
                                'product_number',
                            ],
                            'with' => 'media',
                        ],
                    ]"
                />
                <div
                    x-transition
                    x-cloak
                    x-ref="quickAddAmount"
                    x-show="$wire.orderPosition.product_id"
                    class="min-w-28"
                >
                    <x-number
                        :step="0.01"
                        :label="__('Amount')"
                        wire:model="orderPosition.amount"
                    />
                </div>
                @show
            </div>
            <div class="flex w-full items-center justify-end gap-2 pt-2">
                @section('order-positions-footer-card.buttons')
                <div
                    x-transition
                    x-cloak
                    x-show="$wire.orderPosition.product_id"
                >
                    <x-button
                        class="whitespace-nowrap"
                        color="emerald"
                        icon="plus"
                        :text="__('Quick add')"
                        type="submit"
                    />
                </div>
                <div>
                    <x-button
                        class="whitespace-nowrap"
                        :text="__('Add Detailed')"
                        color="indigo"
                        icon="pencil"
                        x-ref="addPosition"
                        wire:click="editOrderPosition().then(() => $modalOpen('edit-order-position'))"
                    />
                </div>
                @show
            </div>
        </form>
    </x-card>
</div>
@show
