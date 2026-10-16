<div>
    <x-modal
        id="edit-product-configuration-group-modal"
        :title="__('Product Configuration Group')"
        size="4xl"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="productConfigurationGroupForm.name"
                :label="__('Name')"
            />
            <x-textarea
                wire:model="productConfigurationGroupForm.description"
                :label="__('Description')"
            />
            <div class="grid grid-cols-2 gap-4">
                <x-select.native
                    wire:model="productConfigurationGroupForm.selection_type_enum"
                    :label="__('Selection Type Enum')"
                    select="label:label|value:value"
                    :options="$selectionTypes"
                />
                <x-select.native
                    wire:model="productConfigurationGroupForm.price_mode_enum"
                    :label="__('Price Mode Enum')"
                    select="label:label|value:value"
                    :options="$priceModes"
                />
            </div>
            <div
                class="grid grid-cols-2 gap-4"
                x-cloak
                x-show="
                    $wire.productConfigurationGroupForm.selection_type_enum ===
                    'multiple'
                "
            >
                <x-number
                    wire:model="productConfigurationGroupForm.min_selections"
                    :label="__('Min selections')"
                    min="0"
                />
                <x-number
                    wire:model="productConfigurationGroupForm.max_selections"
                    :label="__('Max selections')"
                    min="1"
                />
            </div>
            <div
                class="flex flex-col gap-2"
                x-cloak
                x-show="$wire.productConfigurationGroupForm.id"
            >
                <div class="flex items-center justify-between">
                    <x-label :label="__('Options')" />
                    <x-button
                        color="indigo"
                        icon="plus"
                        :text="__('Add option')"
                        x-on:click="$wire.editItem()"
                    />
                </div>
                <template x-for="item in $wire.items" x-bind:key="item.id">
                    <div
                        class="flex items-center justify-between gap-4 border-b py-2"
                    >
                        <div class="flex flex-col">
                            <span
                                class="font-semibold"
                                x-text="item.product_name"
                            ></span>
                            <span
                                class="text-sm text-gray-500"
                                x-text="
                                    item.rules
                                        .map(
                                            (rule) =>
                                                rule.rule_type +
                                                ' ' +
                                                rule.target,
                                        )
                                        .join(', ')
                                "
                            ></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span x-text="item.surcharge"></span>
                            <x-button
                                color="indigo"
                                icon="pencil"
                                x-on:click="$wire.editItem(item.id)"
                            />
                            <x-button
                                color="red"
                                icon="trash"
                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Option')]) }}"
                                wire:click="deleteItem(item.id)"
                            />
                        </div>
                    </div>
                </template>
            </div>
            <div
                class="text-sm text-gray-500"
                x-cloak
                x-show="!$wire.productConfigurationGroupForm.id"
            >
                {{ __('Options can be added once the group is saved.') }}
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('edit-product-configuration-group-modal')
                "
            />
            <x-button
                color="indigo"
                loading="save"
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'edit-product-configuration-group-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
    <x-modal
        id="edit-product-configuration-group-item-modal"
        :title="__('Option')"
    >
        <div class="flex flex-col gap-4">
            <x-select.styled
                :label="__('Product')"
                wire:model="productConfigurationGroupItemForm.product_id"
                required
                select="label:label|value:id|description:product_number"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Product::class),
                    'method' => 'POST',
                    'params' => [
                        'fields' => [
                            'id',
                            'name',
                            'product_number',
                        ],
                        'where' => [
                            ['is_configurable', '=', false],
                            ['is_bundle', '=', false],
                            ['is_variant_parent', '=', false],
                        ],
                        'with' => 'media',
                    ],
                ]"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.surcharge"
                :label="__('Surcharge')"
                step="0.01"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.max_amount"
                :label="__('Max quantity')"
                min="1"
            />
            <x-number
                wire:model="productConfigurationGroupItemForm.sort"
                :label="__('Sort')"
                min="0"
            />
            <div
                class="flex flex-col gap-2"
                x-cloak
                x-show="$wire.productConfigurationGroupItemForm.id"
            >
                <div class="flex items-center justify-between">
                    <x-label :label="__('Rules')" />
                    <x-button
                        color="indigo"
                        icon="plus"
                        :text="__('Add rule')"
                        x-on:click="
                            $wire.editRule(
                                $wire.productConfigurationGroupItemForm.id,
                            )
                        "
                    />
                </div>
                <template
                    x-for="
                        rule in
                        ($wire.items.find(
                            (item) =>
                                item.id ===
                                $wire.productConfigurationGroupItemForm.id,
                        )?.rules ?? [])
                    "
                    x-bind:key="rule.id"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span
                            x-text="rule.rule_type + ' ' + rule.target"
                        ></span>
                        <div class="flex gap-2">
                            <x-button
                                color="indigo"
                                icon="pencil"
                                x-on:click="
                                    $wire.editRule(
                                        $wire.productConfigurationGroupItemForm
                                            .id,
                                        rule.id,
                                    )
                                "
                            />
                            <x-button
                                color="red"
                                icon="trash"
                                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Rule')]) }}"
                                wire:click="deleteRule(rule.id)"
                            />
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal(
                        'edit-product-configuration-group-item-modal',
                    )
                "
            />
            <x-button
                color="indigo"
                loading="saveItem"
                :text="__('Save')"
                x-on:click="
                    $wire.saveItem().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'edit-product-configuration-group-item-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
    <x-modal id="edit-product-configuration-rule-modal" :title="__('Rule')">
        <div
            class="flex flex-col gap-4"
            x-data="{ wholeGroup: true }"
            x-init="
                $watch('$wire.ruleModalOpens', () => {
                    wholeGroup =
                        $wire.productConfigurationRuleForm.target_items
                            .length === 0;
                })
            "
        >
            <x-select.native
                wire:model="productConfigurationRuleForm.rule_type_enum"
                :label="__('Rule Type Enum')"
                select="label:label|value:value"
                :options="$ruleTypes"
            />
            <x-toggle
                x-model="wholeGroup"
                :label="__('Whole group')"
                x-on:change="
                    wholeGroup
                        ? ($wire.productConfigurationRuleForm.target_items = [])
                        : ($wire.productConfigurationRuleForm.target_group_id =
                              null)
                "
            />
            <div class="flex flex-col gap-2" x-cloak x-show="wholeGroup">
                <x-label :label="__('Target group')" />
                <template
                    x-for="group in $wire.ruleOptions.groups"
                    x-bind:key="group.id"
                >
                    <label class="flex items-center gap-2">
                        <x-radio
                            name="rule-target-group"
                            x-bind:value="group.id"
                            x-model.number="
                                $wire.productConfigurationRuleForm
                                    .target_group_id
                            "
                        />
                        <span x-text="group.name"></span>
                    </label>
                </template>
            </div>
            <div class="flex flex-col gap-2" x-cloak x-show="!wholeGroup">
                <x-label :label="__('Target options')" />
                <template
                    x-for="option in $wire.ruleOptions.items"
                    x-bind:key="option.id"
                >
                    <label class="flex items-center gap-2">
                        <x-checkbox
                            x-bind:value="option.id"
                            x-model.number="
                                $wire.productConfigurationRuleForm.target_items
                            "
                        />
                        <span x-text="option.name"></span>
                    </label>
                </template>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="
                    $tsui.close.modal('edit-product-configuration-rule-modal')
                "
            />
            <x-button
                color="indigo"
                loading="saveRule"
                :text="__('Save')"
                x-on:click="
                    $wire.saveRule().then((success) => {
                        if (success)
                            $tsui.close.modal(
                                'edit-product-configuration-rule-modal',
                            );
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
