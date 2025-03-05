<div>
    <div class="grid grid-cols-1 gap-1.5 sm:grid-cols-6" x-data="{isNew: @entangle('isNew')}">
        <div class="sm:col-span-6">
            <x-input label="{{ __('Name') }}"
                     placeholder="{{ __('Name') }}"
                     wire:model="ticketType.name"/>
        </div>
        <div class="sm:col-span-6" x-show="isNew" x-transition x-cloak>
            <x-select.styled
                label="{{ __('Model') }}"
                placeholder="{{ __('Model') }}"
                wire:model="ticketType.model_type"
                select="label:value|value:label"
                :options="$models"
            />
        </div>
        <div class="sm:col-span-6">
            <x-select.styled
                label="{{ __('Notifications') }}"
                placeholder="{{ __('Roles') }}"
                wire:model="ticketType.roles"
                multiple
                select="label:name|value:id"
                :options="$roles"
            />
        </div>
    </div>
</div>
