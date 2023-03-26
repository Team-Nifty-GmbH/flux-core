<div
    class="relative"
    wire:ignore
    x-data="{
        showSidebar: false,
        cols: @js($enabledCols),
        enabledCols: @js($availableCols),
        colLabels: @js($colLabels),
        sortable: @js($sortable),
        filterable: @js($filterable),
        selectable: @js($selectable),
        stretchCol: @js($stretchCol),
        records: $wire.entangle('{{ $model }}'),
        getData() {
            if (this.records.hasOwnProperty('data')) {
                return this.records.data;
            }

            return this.records;
        },
        formatters: @js($formatters),
        formatter(col, record) {
            const val = _.get(record, col, null);

            if (@js($indentedCols).includes(col)) {
                return `<span class='${ record.depth >= 1 ? 'indent-icon' : '' }' style='text-indent:${ record.depth * 10 }px;'>` + val + '</span>';
            }

            if (this.formatters.hasOwnProperty(col)) {
                const type = this.formatters[col];

                return formatters.format({value: val, type: type, context: record});
            }

            return val;
        },
        disabled(record) {
            return ! {{ $attributes->whereStartsWith('x-disabled')->first('false') }};
        },
        selected: $wire.get('{{ $attributes->wire('selectable')->value() }}') && $wire.entangle('{{ $attributes->wire('selectable')->value() }}').defer ,
    }"
>
    <x-sidebar x-on:keydown.esc="showSidebar = false" x-show="showSidebar">
        {{ $sidebar ?? '' }}
        <x-slot:footer>
            {{ $sidebarFooter ?? '' }}
        </x-slot:footer>
    </x-sidebar>
    <div class="flex w-full">
        {{ $searchbar ?? '' }}
    </div>
    @if($actions ?? false)
        <x-dropdown>
            {{ $actions }}
        </x-dropdown>
    @endif
        <x-spinner wire:loading.delay />
        <x-table {{ $attributes->thatStartWith('wire:sortable') }}>
            <x-slot:header>
                @if($selectable)
                    <x-table.head-cell class="w-4">
                        <x-checkbox x-on:change="function (e) {
                            if (e.target.checked) {
                                selected = records.map(record => record.id);
                                selected.push('*');
                            } else {
                                selected = [];
                            }
                        }" value="*" x-model="selected"/>
                    </x-table.head-cell>
                @endif
                @if($showIndex)
                    <x-table.head-cell class="w-[1%]">
                        {{ __('Pos.') }}
                    </x-table.head-cell>
                @endif
                <template x-for="(col, index) in cols">
                    <x-table.head-cell x-bind:class="stretchCol.length && ! stretchCol.includes(col) ? 'w-[1%]' : ''">
                        <div class="flex justify-between">
                            <div x-bind:class="sortable[col] ? 'cursor-pointer' : ''" class="flex">
                                <span x-on:click="sortable[col] && $wire.sortTable(col)" x-text="colLabels[col]"></span>
                                <x-icon
                                    x-bind:class="Object.keys(sortable).length && orderByCol === col
                                    ? (orderAsc || 'rotate-180')
                                    : 'opacity-0'"
                                    name="chevron-down"
                                    class="h-4 w-4 transition-all"
                                />
                            </div>
                        </div>
                    </x-table.head-cell>
                </template>
                @if($rowActions ?? false)
                    <x-table.head-cell class="w-[1%]">
                        {{ __('Actions') }}
                    </x-table.head-cell>
                @endif
                @if(($enabledCols ?? false) || ($filterable ?? false))
                    <x-table.head-cell class="flex w-4 w-full flex-row-reverse">
                        <div class="flex w-full flex-row-reverse items-center">
                            @if($enabledCols ?? false)
                                <x-dropdown persistent>
                                    <template x-for="col in enabledCols">
                                        <div>
                                            <label x-bind:for="col" class="flex items-center">
                                                <div class="relative flex items-start">
                                                    <div class="flex h-5 items-center">
                                                        <x-checkbox x-bind:id="col" x-bind:value="col" x-model="cols" />
                                                    </div>
                                                    <div class="ml-2 text-sm">
                                                        <label x-text="colLabels[col]" class="block text-sm font-medium text-gray-700 dark:text-gray-50" x-bind:for="col">
                                                        </label>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </template>
                                </x-dropdown>
                            @endif
                            <template x-if="Object.keys(filterable).length">
                                <x-button icon="filter" x-on:click="showSidebar = true; $dispatch('show-datatable-sidebar')"/>
                            </template>
                        </div>
                    </x-table.head-cell>
                @endif
            </x-slot:header>
            <template x-if="! getData().length">
                <tr>
                    <td colspan="100%" class="h-24 w-24 p-8">
                        <div class="w-full flex-col items-center dark:text-gray-50">
                            <x-icon name="emoji-sad" class="m-auto h-24 w-24" />
                            <div class="text-center">
                                {{ __('No data found') }}
                            </div>
                        </div>
                    </td>
                </tr>
            </template>
            <template x-for="(record, index) in getData()">
                <x-table.row
                    wire:sortable.item
                    {{ $attributes->whereStartsWith(['x-bind:class.tr']) }}
                    x-bind:data-slug="record.slug_position"
                    x-bind:data-parent-id="record.parent_id"
                    x-bind:data-id="record.id"
                    x-bind:data-level="record.slug_position?.split('.').length - 1"
                    x-bind:key="record.id"
                >
                    <template x-if="selectable">
                        <div {{ $attributes->whereStartsWith(['x-bind:class.td']) }} class="table-cell whitespace-nowrap border-b border-slate-200 px-3 py-4 text-sm dark:border-slate-600">
                            <template x-if="disabled(record)">
                                <x-checkbox x-bind:value="record.id" x-model="selected"/>
                            </template>
                        </div>
                    </template>
                    @if($showIndex)
                        <div {{ $attributes->whereStartsWith('x-bind:class.td') }} class="table-cell whitespace-nowrap border-b border-slate-200 px-3 py-4 text-sm dark:border-slate-600" x-text="index + 1"></div>
                    @endif
                    <template x-for="col in cols">
                        <x-table.cell {{ $attributes->whereStartsWith(['x-bind:class.td', 'x-bind:href', 'x-on:click']) }}>
                            <div class="flex" x-html="formatter(col, record)">
                            </div>
                        </x-table.cell>
                    </template>
                    @if($rowActions ?? false)
                        <td {{ $attributes->whereStartsWith(['x-bind:class.td']) }} class="whitespace-nowrap border-b border-slate-200 px-3 py-4 dark:border-slate-600">
                            <template x-if="disabled(record)">
                                <div class="flex">
                                    {{ $rowActions }}
                                </div>
                            </template>
                        </td>
                    @endif
                    {{-- Empty cell for the col selection--}}
                    <td {{ $attributes->whereStartsWith('x-bind:class.td') }} class="table-cell whitespace-nowrap border-b border-slate-200 px-3 py-4 text-sm dark:border-slate-600">
                    </td>
                </x-table.row>
            </template>
                <x-slot:footer>
                    <template x-if="$wire.get('{{ $model }}').hasOwnProperty('current_page') ">
                        <td colspan="100%">
                            <div class="flex items-center justify-between px-4 py-3 sm:px-6">
                                <div class="flex flex-1 justify-between sm:hidden">
                                    <x-button
                                        x-bind:disabled="{{ $model }}.current_page === 1"
                                        x-on:click="$wire.set('page', {{ $model }}.current_page + 1)"
                                    >{{ __('Previous') }}</x-button>
                                    <x-button
                                        x-bind:disabled="{{ $model }}.current_page === {{ $model }}.last_page"
                                        x-on:click="$wire.set('page', {{ $model }}.current_page + 1)"
                                    >{{ __('Next') }}</x-button>
                                </div>
                                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex gap-1 text-sm text-slate-400">
                                            {{ __('Showing') }}
                                            <div x-text="{{ $model }}.from" class="align-middle font-medium"></div>
                                            {{ __('to') }}
                                            <div x-text="{{ $model }}.to" class="font-medium"></div>
                                            {{ __('of') }}
                                            <div x-text="{{ $model }}.total" class="font-medium"></div>
                                            {{ __('results') }}
                                            @if($this->perPage ?? false)
                                                <x-select class="pl-4" wire:model="perPage" :clearable="false"
                                                          option-value="value"
                                                          option-label="label"
                                                          :options="[
                                                        ['value' => 15, 'label' => '15'],
                                                        ['value' => 50, 'label' => '50'],
                                                        ['value' => 100, 'label' => '100'],
                                                    ]"
                                                />
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <nav class="isolate inline-flex space-x-1 rounded-md shadow-sm" aria-label="Pagination">
                                            <x-button
                                                x-bind:disabled="{{ $model }}.current_page === 1"
                                                x-on:click="$wire.set('page', {{ $model }}.current_page - 1)"
                                                icon="chevron-left"
                                            />
                                            <template x-for="link in {{ $model }}.links">
                                                <x-button
                                                    x-bind:disabled="link.active"
                                                    x-html="link.label"
                                                    x-on:click="$wire.set('page', link.label)"
                                                    x-bind:class="link.active && 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'"
                                                />
                                            </template>
                                            <x-button
                                                x-bind:disabled="{{ $model }}.current_page === {{ $model }}.last_page"
                                                x-on:click="$wire.set('page', {{ $model }}.current_page + 1)"
                                                icon="chevron-right"
                                            />
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </template>
                </x-slot:footer>
        </x-table>
    {{ $slot ?? '' }}
</div>
