<div x-data="{
    get modal() {
        return Alpine.$data($el.querySelector('[wireui-modal]'))
    },
    create() {
        $wire.edit().then(() => {
            this.modal.open();
        });
    },
    edit(record) {
        $wire.edit(record).then(() => {
            this.modal.open();
        });
    },
    save() {
        $wire.save().then((success) => {
            if (success) {
                this.modal.close();
            }
        });
    },
    category: $wire.$entangle('category'),
}">
    <div id="category-modal">
        <x-modal.card :title="$category->id ? __('Edit Category') : __('Create Category')">
            <div class="flex flex-col gap-4">
                <x-input wire:model="category.name" :label="__('Name')"></x-input>
                <x-toggle wire:model="category.is_active" :label="__('Active')"></x-toggle>
                <x-select
                        label="{{ __('Model') }}"
                        placeholder="{{ __('Model') }}"
                        wire:model="category.model_type"
                        :options="$models"
                        option-label="label"
                        option-value="value"
                        :clearable="false"
                />
                <x-select
                        wire:model="category.parent_id"
                        :label="__('Categories')"
                        option-value="id"
                        option-label="label"
                        option-description="description"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Category::class),
                            'method' => 'POST',
                            'params' => [
                                'where' => [
                                    [
                                        'model_type',
                                        '=',
                                        $category->model_type,
                                    ]
                                ],
                            ],
                        ]"
                />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(resolve_static(\FluxErp\Actions\Category\DeleteCategory::class, 'canPerformAction', [false]))
                        <div x-bind:class="$wire.category.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Category')]) }}"
                                wire:click="delete().then((success) => {if(success) close();});"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();});"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-modal.card>
    </div>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
