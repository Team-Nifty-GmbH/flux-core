<x-tall-datatables::data-table-wrapper :attributes="$componentAttributes" >
    <ul x-sort="$wire.reOrder($item, $position)" class="flex flex-col gap-1.5">
        <template x-for="(record, index) in getData()" :key="{{ $selectValue }}">
            <li x-sort:item="record.id">
                <x-card>
                    <div class="flex gap-1.5 w-full">
                        <template x-for="col in enabledCols">
                            <div class="cursor-move">
                                <div class="flex gap-1.5 flex-wrap">
                                    <div class="flex flex-wrap gap-1" x-html="formatter(leftAppend[col], record)">
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex flex-wrap gap-1" x-html="formatter(topAppend[col], record)">
                                        </div>
                                        <div class="flex flex-wrap gap-1" {{ $cellAttributes->merge(['x-html' => 'formatter(col, record)']) }}>
                                        </div>
                                        <div class="flex flex-wrap gap-1" x-html="formatter(bottomAppend[col], record)">
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-1" x-html="formatter(rightAppend[col], record)">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-card>
            </li>
        </template>
    </ul>
</x-tall-datatables::data-table-wrapper>
