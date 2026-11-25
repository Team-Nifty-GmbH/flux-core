<div>
    @if ($mainOrderId)
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Main Order') }}:
            <a
                href="{{ route('orders.id', $mainOrderId) }}"
                wire:navigate
                class="text-primary-600 dark:text-primary-400 font-medium"
            >
                {{ $mainOrderLabel }}
            </a>
        </div>
    @endif

    <x-modal
        id="add-supplementary-order-modal"
        :title="__('Add Supplementary Order')"
    >
        <x-select.styled
            wire:model="supplementaryOrders"
            :label="__('Orders')"
            select="value:id"
            multiple
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Order::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        ['contact_id', '=', $contactId],
                    ],
                ],
            ]"
        />

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$tsui.close.modal('add-supplementary-order-modal')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="addSupplementaryOrders()"
            />
        </x-slot:footer>
    </x-modal>
</div>
