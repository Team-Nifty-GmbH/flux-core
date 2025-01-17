<x-modal name="edit-order-type">
    <x-card>
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                    <div class="space-y-3 sm:col-span-6">
                        <x-input wire:model="orderType.name" :label="__('Order Type Name')"/>
                        <x-textarea wire:model="orderType.description" :label="__('Description')"/>
                        <x-select
                            :label="__('Client')"
                            :placeholder="__('Select a Client')"
                            wire:model="orderType.client_id"
                            :options="$clients"
                            option-label="name"
                            option-value="id"
                        />
                        <x-select
                            :label="__('Order Type')"
                            :disabled="(bool) $orderType->id"
                            :placeholder="__('Select Order Type')"
                            wire:model="orderType.order_type_enum"
                            :options="$enum"
                        />
                        <x-select
                            :label="__('Print Layouts')"
                            :placeholder="__('Select Print Layouts')"
                            wire:model="orderType.print_layouts"
                            multiselect
                            option-label="label"
                            option-value="value"
                            :options="$printViews"
                        />
                        <x-select
                            :label="__('Post stock')"
                            :hint="__('Stock will be posted on creation of given documents')"
                            wire:model="orderType.post_stock_print_layouts"
                            multiselect
                            option-label="label"
                            option-value="value"
                            :options="$printViews"
                        />
                        <x-select
                            :label="__('Reserve stock')"
                            :hint="__('Stock will be reserved on creation of given documents.') . ' ' . __('Stock posting has priority over stock reservation.')"
                            wire:model="orderType.reserve_stock_print_layouts"
                            multiselect
                            option-label="label"
                            option-value="value"
                            :options="$printViews"
                        />
                        <x-checkbox wire:model="orderType.is_active" :label="__('Is Active')"/>
                        <x-checkbox wire:model="orderType.is_hidden" :label="__('Is Hidden')"/>
                        <x-checkbox wire:model="orderType.is_visible_in_sidebar" :label="__('Is Visible In Sidebar')"/>
                        <x-input wire:model="orderType.mail_subject" :label="__('Mail Subject')"/>
                        <x-flux::editor wire:model="orderType.mail_body" :label="__('Mail Body')"/>
                    </div>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div x-bind:class="$wire.orderType.id > 0 || 'invisible'">
                    <x-button
                        flat
                        negative
                        :label="__('Delete')"
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Order Type')]) }}"
                        wire:click="delete().then((success) => {if (success) close();})"
                    />
                </div>
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button
                        primary
                        :label="__('Save')"
                        wire:click="save().then((success) => {if (success) close();})"
                    />
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
