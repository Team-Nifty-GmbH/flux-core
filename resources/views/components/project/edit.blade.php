<div>
    <x-errors />
    <x-input x-bind:readonly="!edit" wire:model="project.name" label="{{ __('Name') }}" />
    <div class="flex justify-between">
        <x-state
            class="w-full"
            align="left"
            :label="__('Project state')"
            wire:model="project.state"
            formatters="formatter.state"
            available="availableStates"
        />
        <x-select
            :label="__('Contact')"
            class="pb-4"
            wire:model="project.contact_id"
            option-value="contact_id"
            option-label="label"
            option-description="description"
            template="user-option"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Address::class),
                'params' => [
                    'fields' => [
                        'contact_id',
                        'firstname',
                        'lastname',
                        'company',
                        'name',
                    ],
                    'where' => [
                        [
                            'is_main_address',
                            '=',
                            true,
                        ]
                    ],
                    'with' => 'contact.media',
                ]
            ]"
        />
        <x-select
            :label="__('Order')"
            class="pb-4"
            wire:model="project.order_id"
            option-value="id"
            option-label="label"
            option-description="description"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Order::class),
            ]"
        />
        <x-input type="date" x-bind:readonly="!edit" wire:model="project.start_date" label="{{ __('Start Date') }}" />
        <x-input type="date" x-bind:readonly="!edit" wire:model="project.end_date" label="{{ __('End Date') }}" />
    </div>
    <x-textarea x-bind:readonly="!edit" wire:model="project.description" label="{{ __('Description') }}" />
    <x-inputs.number :label="__('Budget')" x-bind:readonly="!edit" wire:model="project.budget" />
    <x-inputs.number :label="__('Time Budget in hours')" x-bind:readonly="!edit" wire:model="project.time_budget_hours" />
</div>
