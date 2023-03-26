<div
    x-data="{
        additionalColumns: @js($additionalColumns),
        table: @js($table),
        record: @entangle($wire).defer,
        user: @js(auth()->user() instanceof \FluxErp\Models\User)
    }"
>
    <div x-bind:class="table && 'table w-full table-auto border-spacing-y-3'" class="space-y-3">
        <template x-for="additionalColumn in additionalColumns" :key="additionalColumn.name">
            <div x-bind:class="table && 'table-row-group'">
                <div x-bind:class="table && 'table-row'">
                    <x-label x-bind:class="table && 'table-cell'" x-html="additionalColumn.label ? additionalColumn.label : additionalColumn.name" x-bind:for="additionalColumn.name" />
                    <div x-bind:class="table && 'table-cell'">
                        <template x-if="additionalColumn.is_customer_editable || user">
                            <x-input x-bind:id="additionalColumn.name" x-bind:type="additionalColumn.field_type" x-model="record[additionalColumn.name]" />
                        </template>
                        <template x-if="!additionalColumn.is_customer_editable && !user">
                            <span x-text="record[additionalColumn.name]" x-bind:for="additionalColumn.name" />
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
