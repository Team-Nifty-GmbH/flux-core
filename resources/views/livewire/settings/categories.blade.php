<div x-data="{
    setCategorySearch() {
        let component = Alpine.$data(document.getElementById('category-parent-id').querySelector('[x-data]'));
        component.request.params.where[0][2] = $wire.category.model_type;
        component.request.params.where[1][2] = $wire.category.id;
    }
}">
    @section('modals')
        <x-modal id="edit-category" x-on:open="setCategorySearch()" :title="$category->id ? __('Edit Category') : __('Create Category')">
            <div class="flex flex-col gap-4">
                @section('modals.edit-category.content')
                    <x-input wire:model="category.name" :label="__('Name')"></x-input>
                    <x-toggle wire:model="category.is_active" :label="__('Active')"></x-toggle>
                    <div x-bind:class="$wire.category.id && 'pointer-events-none'">
                        <x-select.styled
                            x-bind:disabled="$wire.category.id"
                            label="{{ __('Model') }}"
                            placeholder="{{ __('Model') }}"
                            wire:model="category.model_type"
                            :options="$models"
                            select="label:value|value:label"
                            required
                        />
                    </div>
                    <div id="category-parent-id">
                        <x-select.styled
                            wire:model="category.parent_id"
                            :label="__('Parent')"
                            select="label:label|value:id"
                            option-description="description"
                            :request="[
                            'url' => route('search', \FluxErp\Models\Category::class),
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
                    <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-category')"/>
                    <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('edit-category');});"/>
                </div>
            </x-slot:footer>
        </x-modal>
    @show
</div>
