<div x-data="{
    setCategorySearch() {
        let component = Alpine.$data(document.getElementById('category-parent-id').querySelector('[x-data]'));
        component.asyncData.params.where[0][2] = $wire.category.model_type;
        component.asyncData.params.where[1][2] = $wire.category.id;
    }
}">
    @section('modals')
        <x-modal name="edit-category" x-on:open="setCategorySearch()">
            <x-card :title="$category->id ? __('Edit Category') : __('Create Category')">
                <div class="flex flex-col gap-4">
                    @section('modals.edit-category.content')
                        <x-input wire:model="category.name" :label="__('Name')"></x-input>
                        <x-toggle wire:model="category.is_active" :label="__('Active')"></x-toggle>
                        <div x-bind:class="$wire.category.id && 'pointer-events-none'">
                            <x-select
                                x-bind:disabled="$wire.category.id"
                                label="{{ __('Model') }}"
                                placeholder="{{ __('Model') }}"
                                wire:model="category.model_type"
                                :options="$models"
                                option-label="label"
                                option-value="value"
                                :clearable="false"
                            />
                        </div>
                        <div id="category-parent-id">
                            <x-select
                                wire:model="category.parent_id"
                                :label="__('Parent')"
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
                                        ],
                                        [
                                            'id',
                                            '!=',
                                            $category->id,
                                        ],
                                    ],
                                ],
                            ]"
                            />
                        </div>
                    @show
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-x-4">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();});"/>
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    @show
</div>
