<div>
    @foreach($conditionTree as $root)
        @if($root['type'] === 'or_container')
            <div class="space-y-4">
                @foreach($root['children'] as $groupIndex => $andGroup)
                    @if($groupIndex > 0)
                        <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                            <div class="flex-1 border-t"></div>
                            {{ __('OR') }}
                            <div class="flex-1 border-t"></div>
                        </div>
                    @endif

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ __('AND Group') }}
                            </span>
                            <button
                                type="button"
                                class="text-red-500 hover:text-red-700"
                                wire:click="removeOrGroup({{ $andGroup['id'] }})"
                            >
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="space-y-2">
                            @foreach($andGroup['children'] as $condition)
                                <div
                                    class="flex items-start gap-2 rounded border border-gray-200 bg-white p-3 dark:border-gray-600 dark:bg-gray-700"
                                    x-data="{ value: @js($condition['value']) }"
                                >
                                    <div class="flex-1">
                                        @php
                                            $registry = app(\FluxErp\RuleEngine\ConditionRegistry::class);
                                            $conditionClass = $registry->all()[$condition['type']] ?? null;
                                            $schema = $conditionClass ? $conditionClass::schema() : [];
                                        @endphp
                                        <div class="mb-2 text-sm font-medium">
                                            {{ $conditionClass ? $conditionClass::label() : $condition['type'] }}
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($schema as $fieldName => $fieldDef)
                                                @if(data_get($fieldDef, 'type') === 'select')
                                                    <x-select.native
                                                        x-model="value.{{ $fieldName }}"
                                                        :label="data_get($fieldDef, 'label', $fieldName)"
                                                        x-on:change="$wire.updateConditionValue({{ $condition['id'] }}, value)"
                                                    >
                                                        @foreach(data_get($fieldDef, 'options', []) as $optValue => $optLabel)
                                                            <option value="{{ $optValue }}">{{ $optLabel }}</option>
                                                        @endforeach
                                                    </x-select.native>
                                                @elseif(data_get($fieldDef, 'type') === 'date')
                                                    <x-input
                                                        type="date"
                                                        x-model="value.{{ $fieldName }}"
                                                        :label="data_get($fieldDef, 'label', $fieldName)"
                                                        x-on:change="$wire.updateConditionValue({{ $condition['id'] }}, value)"
                                                    />
                                                @elseif(data_get($fieldDef, 'type') === 'time')
                                                    <x-input
                                                        type="time"
                                                        x-model="value.{{ $fieldName }}"
                                                        :label="data_get($fieldDef, 'label', $fieldName)"
                                                        x-on:change="$wire.updateConditionValue({{ $condition['id'] }}, value)"
                                                    />
                                                @elseif(data_get($fieldDef, 'type') === 'number')
                                                    <x-number
                                                        x-model="value.{{ $fieldName }}"
                                                        :label="data_get($fieldDef, 'label', $fieldName)"
                                                        x-on:change="$wire.updateConditionValue({{ $condition['id'] }}, value)"
                                                    />
                                                @else
                                                    <x-input
                                                        x-model="value.{{ $fieldName }}"
                                                        :label="data_get($fieldDef, 'label', $fieldName)"
                                                        x-on:change="$wire.updateConditionValue({{ $condition['id'] }}, value)"
                                                    />
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="mt-1 text-red-500 hover:text-red-700"
                                        wire:click="removeCondition({{ $condition['id'] }})"
                                    >
                                        <x-icon name="x-mark" class="h-4 w-4" />
                                    </button>
                                </div>
                            @endforeach

                            <div x-data="{ open: false }" class="relative">
                                <button
                                    type="button"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                                    x-on:click="open = !open"
                                >
                                    + {{ __('Add Condition') }}
                                </button>
                                <div
                                    x-show="open"
                                    x-cloak
                                    x-on:click.outside="open = false"
                                    class="absolute left-0 z-10 mt-1 w-64 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    @foreach($conditionTypes as $groupName => $types)
                                        <div class="border-b px-3 py-1 text-xs font-semibold uppercase text-gray-400 dark:border-gray-600">
                                            {{ __($groupName) }}
                                        </div>
                                        @foreach($types as $type => $class)
                                            <button
                                                type="button"
                                                class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                wire:click="addCondition({{ $andGroup['id'] }}, '{{ $type }}')"
                                                x-on:click="open = false"
                                            >
                                                {{ $class::label() }}
                                            </button>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <button
                    type="button"
                    class="w-full rounded-lg border-2 border-dashed border-gray-300 p-3 text-center text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600 dark:border-gray-600 dark:text-gray-400"
                    wire:click="addOrGroup"
                >
                    + {{ __('Add OR Group') }}
                </button>
            </div>
        @endif
    @endforeach

    @if(empty($conditionTree))
        <div class="text-center text-sm text-gray-500">
            <p class="mb-2">{{ __('No conditions defined. This rule will always match.') }}</p>
            <button
                type="button"
                class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                wire:click="addOrGroup"
            >
                + {{ __('Add OR Group') }}
            </button>
        </div>
    @endif
</div>
