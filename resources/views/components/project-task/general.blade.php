<div class="space-y-5">
    <x-input :label="__('Name')" x-model="projectTask.name" />
    <x-state
        class="w-full"
        align="left"
        :label="__('State')"
        wire:model.live="projectTask.state"
        formatters="formatter.state"
        available="availableStates"
    />
    <x-select
        wire:model.live="projectTask.user_id"
        :label="__('User')"
        option-value="id"
        option-label="label"
        option-description="description"
        :async-data="[
            'api' => route('search', \FluxErp\Models\User::class),
            'method' => 'POST',
        ]"
    />
    <x-select
        wire:model.live="projectTask.address_id"
        :label="__('Customer')"
        option-value="id"
        option-label="label"
        option-description="description"
        :async-data="[
            'api' => route('search', \FluxErp\Models\Address::class),
            'method' => 'POST',
        ]"
    />
    @if($this->additionalColumns ?? false)
        @foreach($this->additionalColumns as $additionalColumn)
            @if($additionalColumn['values'] ?? false)
                <x-select
                    x-bind:readonly="!edit"
                    x-model="project.{{ $additionalColumn['name'] }}"
                    :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    :options="$additionalColumn['values']"
                />
            @elseif($additionalColumn['field_type'] === 'checkbox')
                <x-checkbox
                    x-bind:readonly="!edit"
                    x-model="project.{{ $additionalColumn['name'] }}"
                    :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                />
            @else
                <x-input type="{{ $additionalColumn['field_type'] ?? 'text' }}"
                         x-bind:readonly="!edit"
                         x-model="project.{{ $additionalColumn['name'] }}"
                         :label="__($additionalColumn['label'])"
                />
            @endif
        @endforeach
    @endif
</div>
