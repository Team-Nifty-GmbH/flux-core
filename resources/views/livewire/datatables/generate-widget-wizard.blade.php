<div class="py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
            {{ __('Generate Widget') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Step :current of :total', ['current' => $step, 'total' => 4]) }}
        </p>
    </div>

    <x-card>
        {{-- Step 1: Filter Review --}}
        <div x-show="$wire.step === 1" x-cloak>
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Active Filters') }}
            </h3>
            @if (count($userFilters) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($userFilters as $groupIndex => $group)
                        @if ($groupIndex > 0)
                            <x-badge color="emerald" :text="__('or')" />
                        @endif
                        @foreach ($group as $filterIndex => $filter)
                            <x-badge
                                color="primary"
                                :text="data_get($filter, 'column', '') . ' ' . data_get($filter, 'operator', '') . ' ' . (is_array(data_get($filter, 'value')) ? implode(', ', data_get($filter, 'value')) : data_get($filter, 'value', ''))"
                            />
                            @if (! $loop->last)
                                <x-badge color="red" :text="__('and')" />
                            @endif
                        @endforeach
                    @endforeach
                </div>
            @else
                <p class="text-sm italic text-gray-500 dark:text-gray-400">
                    {{ __('No filters set — all data will be included.') }}
                </p>
            @endif
        </div>

        {{-- Step 2: Widget Type Selection --}}
        <div x-show="$wire.step === 2" x-cloak>
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Select Widget Type') }}
            </h3>
            <div class="grid grid-cols-2 gap-4">
                @php
                    $types = [
                        ['key' => 'value_box', 'icon' => 'calculator', 'label' => __('Value Box'), 'description' => __('Single aggregate value')],
                        ['key' => 'bar_chart', 'icon' => 'chart-bar', 'label' => __('Bar Chart'), 'description' => __('Grouped by column')],
                        ['key' => 'line_chart', 'icon' => 'chart-bar-square', 'label' => __('Line Chart'), 'description' => __('Over time')],
                        ['key' => 'value_list', 'icon' => 'list-bullet', 'label' => __('List'), 'description' => __('Top N entries')],
                    ];
                @endphp
                @foreach ($types as $type)
                    <div
                        class="cursor-pointer rounded-lg border-2 p-4 transition-colors hover:border-primary-300 dark:hover:border-primary-700"
                        x-bind:class="$wire.widgetType === '{{ $type['key'] }}' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'"
                        x-on:click="$wire.set('widgetType', '{{ $type['key'] }}')"
                    >
                        <div class="flex flex-col items-center gap-2 text-center">
                            <x-icon :name="$type['icon']" class="h-8 w-8 text-gray-600 dark:text-gray-400" />
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $type['label'] }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $type['description'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step 3: Data Configuration --}}
        <div x-show="$wire.step === 3" x-cloak>
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Data Configuration') }}
            </h3>
            <div class="flex flex-col gap-4">
                {{-- Value Box --}}
                <div x-show="$wire.widgetType === 'value_box'" x-cloak>
                    <div class="flex flex-col gap-4">
                        <x-select.styled
                            searchable
                            wire:model="aggregate"
                            :label="__('Aggregate Function')"
                            :options="[
                                ['value' => 'sum', 'label' => __('Sum')],
                                ['value' => 'avg', 'label' => __('Average')],
                                ['value' => 'min', 'label' => __('Minimum')],
                                ['value' => 'max', 'label' => __('Maximum')],
                                ['value' => 'count', 'label' => __('Count')],
                            ]"
                            select="label:label|value:value"
                        />
                        <div x-show="$wire.aggregate !== 'count'" x-cloak>
                            <x-select.styled
                                searchable
                                wire:model="valueColumn"
                                :label="__('Value Column')"
                                :options="$this->getNumericColumns()"
                                select="label:label|value:name"
                            />
                        </div>
                    </div>
                </div>

                {{-- Bar Chart --}}
                <div x-show="$wire.widgetType === 'bar_chart'" x-cloak>
                    <div class="flex flex-col gap-4">
                        <x-select.styled
                            searchable
                            wire:model="groupColumn"
                            :label="__('Group By Column')"
                            :options="$availableColumns"
                            select="label:label|value:name"
                        />
                        <x-select.styled
                            searchable
                            wire:model="aggregate"
                            :label="__('Aggregate Function')"
                            :options="[
                                ['value' => 'sum', 'label' => __('Sum')],
                                ['value' => 'avg', 'label' => __('Average')],
                                ['value' => 'min', 'label' => __('Minimum')],
                                ['value' => 'max', 'label' => __('Maximum')],
                                ['value' => 'count', 'label' => __('Count')],
                            ]"
                            select="label:label|value:value"
                        />
                        <div x-show="$wire.aggregate !== 'count'" x-cloak>
                            <x-select.styled
                                searchable
                                wire:model="valueColumn"
                                :label="__('Value Column')"
                                :options="$this->getNumericColumns()"
                                select="label:label|value:name"
                            />
                        </div>
                    </div>
                </div>

                {{-- Line Chart --}}
                <div x-show="$wire.widgetType === 'line_chart'" x-cloak>
                    <div class="flex flex-col gap-4">
                        <x-select.styled
                            searchable
                            wire:model="dateColumn"
                            :label="__('Date Column')"
                            :options="$this->getDateColumns()"
                            select="label:label|value:name"
                        />
                        <x-select.styled
                            searchable
                            wire:model="aggregate"
                            :label="__('Aggregate Function')"
                            :options="[
                                ['value' => 'sum', 'label' => __('Sum')],
                                ['value' => 'avg', 'label' => __('Average')],
                                ['value' => 'min', 'label' => __('Minimum')],
                                ['value' => 'max', 'label' => __('Maximum')],
                                ['value' => 'count', 'label' => __('Count')],
                            ]"
                            select="label:label|value:value"
                        />
                        <div x-show="$wire.aggregate !== 'count'" x-cloak>
                            <x-select.styled
                                searchable
                                wire:model="valueColumn"
                                :label="__('Value Column')"
                                :options="$this->getNumericColumns()"
                                select="label:label|value:name"
                            />
                        </div>
                    </div>
                </div>

                {{-- Value List --}}
                <div x-show="$wire.widgetType === 'value_list'" x-cloak>
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Select Columns') }}
                            </label>
                            <div class="flex flex-wrap gap-3">
                                @foreach ($availableColumns as $column)
                                    <x-toggle
                                        wire:model="selectedColumns"
                                        :value="data_get($column, 'name')"
                                        :label="data_get($column, 'label')"
                                    />
                                @endforeach
                            </div>
                        </div>
                        <x-select.styled
                            searchable
                            wire:model="sortColumn"
                            :label="__('Sort Column')"
                            :options="$availableColumns"
                            select="label:label|value:name"
                        />
                        <x-select.styled
                            searchable
                            wire:model="sortDirection"
                            :label="__('Sort Direction')"
                            :options="[
                                ['value' => 'desc', 'label' => __('Descending')],
                                ['value' => 'asc', 'label' => __('Ascending')],
                            ]"
                            select="label:label|value:value"
                        />
                        <x-number
                            wire:model="limit"
                            :label="__('Limit')"
                            min="1"
                            max="100"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Metadata --}}
        <div x-show="$wire.step === 4" x-cloak>
            <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Widget Settings') }}
            </h3>
            <div class="flex flex-col gap-4">
                <x-input
                    wire:model="name"
                    :label="__('Name')"
                    required
                />
                <x-select.styled
                    searchable
                                wire:model="targetDashboard"
                    :label="__('Target Dashboard')"
                    :options="collect($this->getAvailableDashboards)->map(fn ($label, $value) => ['value' => $value, 'label' => $label])->values()->toArray()"
                    select="label:label|value:value"
                    required
                />
                <x-toggle
                    wire:model.live="timeframeAware"
                    :label="__('Bind to Dashboard Timeframe')"
                />
                <div x-show="$wire.timeframeAware" x-cloak>
                    <x-select.styled
                        searchable
                                wire:model="timeframeDateColumn"
                        :label="__('Date Column for Timeframe')"
                        :options="$this->getDateColumns()"
                        select="label:label|value:name"
                    />
                </div>
                @can('widget.generate-share')
                    <x-toggle
                        wire:model="isShared"
                        :label="__('Share with all users')"
                    />
                @endcan
            </div>
        </div>

        <x-slot:footer>
            <div class="flex w-full items-center justify-between">
                <div>
                    @if ($step > 1)
                        <x-button
                            :text="__('Back')"
                            color="secondary"
                            flat
                            wire:click="previousStep"
                        />
                    @endif
                </div>
                <div class="flex gap-2">
                    <x-button
                        :text="__('Cancel')"
                        color="secondary"
                        flat
                        href="{{ url()->previous() }}"
                        wire:navigate
                    />
                    @if ($step < 4)
                        <x-button
                            :text="__('Next')"
                            color="primary"
                            wire:click="nextStep"
                        />
                    @else
                        <x-button
                            :text="__('Create Widget')"
                            color="primary"
                            wire:click="save"
                        />
                    @endif
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</div>
