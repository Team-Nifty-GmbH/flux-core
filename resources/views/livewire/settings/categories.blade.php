<div x-data="{
    setCategorySearch() {
        let component = Alpine.$data(document.getElementById('category-parent-id').querySelector('[x-data]'));
        $tallstackuiSelect('category-parent-id')
                .mergeRequestParams({
                    where: [
                        ['model_type', '=', $wire.category.model_type],
                        ['contact_id', '=', $wire.category.id],
                    ]}
                );
    }
}">
    @section('modals')
        <x-modal id="edit-category-modal" x-on:open="setCategorySearch()" :title="$category->id ? __('Edit Category') : __('Create Category')">
            <div class="flex flex-col gap-1.5">
                @section('modals.edit-category.content')
                    <x-input wire:model="category.name" :label="__('Name')"></x-input>
                    <div class="mt-2">
                        <x-toggle wire:model="category.is_active" :label="__('Active')"></x-toggle>
                    </div>
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
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-category-modal')"/>
                <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('edit-category-modal');});"/>
            </x-slot:footer>
        </x-modal>
    @show
</div>
