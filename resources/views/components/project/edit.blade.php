@props(['collapsed' => false])
<div x-data="{expanded: false}" class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-2.5">
        @section('general')
            <x-input
                :placeholder="__('Leave empty to generate a new :attribute.', ['attribute' => __('Project Number')])"
                x-bind:readonly="!edit"
                wire:model="project.project_number"
                label="{{ __('Project Number') }}"
            />
            <x-input x-bind:readonly="!edit" wire:model="project.name" label="{{ __('Name') }}" />
        @show
        <div @if($collapsed) x-collapse x-show="expanded" x-cloak @endif class="space-y-2.5">
            <div x-bind:class="! edit && 'pointer-events-none'" x-show="! $wire.project.id" x-cloak>
                <x-select
                    x-bind:readonly="!edit"
                    :label="__('Client')"
                    wire:model="project.client_id"
                    option-value="id"
                    option-label="name"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Client::class),
                        'method' => 'POST',
                    ]"
                />
            </div>
            <div class="flex justify-between gap-x-4">
                @section('dates')
                    <x-datetime-picker
                        without-time
                        x-bind:readonly="!edit"
                        x-bind:class="! edit && 'pointer-events-none'"
                        wire:model="project.start_date"
                        :label="__('Start Date')"
                    />
                    <x-datetime-picker
                        without-time
                        x-bind:readonly="!edit"
                        x-bind:class="! edit && 'pointer-events-none'"
                        wire:model="project.end_date"
                        :label="__('End Date')"
                    />
                @show
            </div>
            <div x-bind:class="! edit && 'pointer-events-none'">
                <x-flux::state
                    x-bind:readonly="!edit"
                    class="w-full"
                    align="left"
                    :label="__('Project state')"
                    wire:model="project.state"
                    formatters="formatter.state"
                    available="availableStates"
                />
            </div>
            <x-textarea
                x-bind:readonly="!edit"
                wire:model="project.description"
                :label="__('Description')"
            />
            @section('connections')
                <div x-bind:class="! edit && 'pointer-events-none'">
                    <x-select
                        x-bind:readonly="!edit"
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
                </div>
                <div x-bind:class="! edit && 'pointer-events-none'">
                    <x-select :label="__('Contact')"
                        x-bind:readonly="!edit"
                        wire:model="project.contact_id"
                        option-value="contact_id"
                        option-label="label"
                        template="user-option"
                        :async-data="[
                            'api' => route('search', \FluxErp\Models\Address::class),
                            'method' => 'POST',
                            'params' => [
                                'where' => [
                                    [
                                        'is_main_address',
                                        '=',
                                        true,
                                    ]
                                ],
                                'option-value' => 'contact_id',
                                'fields' => [
                                    'contact_id',
                                    'name',
                                ],
                                'with' => 'contact.media',
                            ]
                        ]"
                    />
                </div>
                <div x-bind:class="! edit && 'pointer-events-none'">
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
                </div>
            @show
            @section('budget')
                <x-number
                    :label="__('Budget')"
                    x-bind:readonly="!edit"
                    wire:model="project.budget"
                    step="0.01"
                />
                <x-input
                    x-bind:readonly="!edit"
                    :label="__('Time Budget')"
                    wire:model.blur="project.time_budget"
                    :corner-hint="__('Hours:Minutes')"
                    placeholder="02:30"
                />
            @show
            @section('additional-columns')
                @if($this->project->additionalColumns)
                    <div class="space-y-2.5">
                        <h3 class="font-medium whitespace-normal text-md text-secondary-700 dark:text-secondary-400 mt-4">
                            {{ __('Additional Columns') }}
                        </h3>
                        <x-flux::additional-columns :model="\FluxErp\Models\Project::class" :id="$this->project->id" wire="project.additionalColumns"/>
                    </div>
                @endif
            @show
        </div>
        @if($collapsed)
            <x-badge outline md label="Prepend" class="w-full cursor-pointer gap-x-4 py-2" x-on:click="expanded = !expanded">
                <x-slot:label>
                    <span x-text="expanded ? '{{ __('Show less') }}' : '{{ __('Show more') }}'"></span>
                </x-slot:label>
                <x-slot:prepend class="relative flex items-center w-2 h-2 transition-transform" x-bind:class="expanded && '-rotate-180'">
                    <x-icon name="chevron-down" class="w-4 h-4 shrink-0" />
                </x-slot:prepend>
            </x-badge>
        @endif
    </div>
    <x-errors />
</div>
