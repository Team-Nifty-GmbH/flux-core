<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-2.5">
        <x-input x-bind:readonly="!edit" wire:model="project.project_number" label="{{ __('Project Number') }}" />
        <x-input x-bind:readonly="!edit" wire:model="project.name" label="{{ __('Name') }}" />
        <div class="flex justify-between gap-x-4">
            <x-input type="date" x-bind:readonly="!edit" wire:model="project.start_date" label="{{ __('Start Date') }}" />
            <x-input type="date" x-bind:readonly="!edit" wire:model="project.end_date" label="{{ __('End Date') }}" />
        </div>
        <x-state
            class="w-full"
            align="left"
            :label="__('Project state')"
            wire:model="project.state"
            formatters="formatter.state"
            available="availableStates"
        />
        <x-textarea x-bind:readonly="!edit" wire:model="project.description" label="{{ __('Description') }}" />
        <x-select
            :label="__('Responsible User')"
            option-value="id"
            option-label="label"
            autocomplete="off"
            wire:model="project.responsible_user_id"
            :template="[
                'name'   => 'user-option',
            ]"
            :async-data="[
                'api' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                ]
            ]"
        />
        <x-select
            :label="__('Contact')"
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
            x-bind:readonly="!edit"
            :label="__('Order')"
            wire:model="project.order_id"
            option-value="id"
            option-label="label"
            option-description="description"
            :async-data="[
                'api' => route('search', \FluxErp\Models\Order::class),
            ]"
        />
        <x-inputs.number :label="__('Budget')" x-bind:readonly="!edit" wire:model="project.budget" step="0.01" />
        <x-input
            x-bind:readonly="!edit"
            :label="__('Time Budget')"
            wire:model.blur="project.time_budget"
            :corner-hint="__('Hours:Minutes')"
            placeholder="02:30"
        />
    </div>
    <div class="space-y-2.5">
        <h3 class="font-medium whitespace-normal text-md text-secondary-700 dark:text-secondary-400 mt-4">
            {{ __('Additional Columns') }}
        </h3>
        <x-additional-columns :model="\FluxErp\Models\Project::class" :id="$this->project->id" wire="project.additionalColumns"/>
    </div>
    <x-errors />
</div>
