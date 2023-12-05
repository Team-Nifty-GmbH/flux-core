<div
    x-data="{
        table: @js($table),
        record: @entangle($wire),
    }"
>
    <div x-bind:class="table && 'table w-full table-auto border-spacing-y-3'" class="space-y-2.5">
        @foreach($additionalColumns as $additionalColumn)
            @if($additionalColumn['is_customer_editable'] || auth()->user() instanceof \FluxErp\Models\User)
                @if($additionalColumn['values'] ?? false)
                    <x-select
                        x-on:selected="$wire.{{ $wire }}['{{ $additionalColumn['name'] }}'] = $event.detail.value"
                        x-model="record['{{ $additionalColumn['name'] }}']"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                        :options="$additionalColumn['values']"
                    />
                @elseif($additionalColumn['field_type'] === 'checkbox')
                    <x-checkbox
                        x-bind:disabled="!edit"
                        x-model="record['{{ $additionalColumn['name'] }}']"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @else
                    <x-input type="{{ $additionalColumn['field_type'] ?? 'text' }}"
                             x-bind:readonly="!edit"
                             x-model="record['{{ $additionalColumn['name'] }}']"
                             :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @endif
            @else
                @if($additionalColumn['field_type'] === 'checkbox')
                    <x-checkbox
                        disabled
                        x-model="record['{{ $additionalColumn['name'] }}']"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @else
                    <x-label x-bind:class="table && 'table-cell'"
                             :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                             x-bind:for="{{ $additionalColumn['name'] }}" />--}}
                    <span x-text="record['{{ $additionalColumn['name'] }}']" x-bind:for="{{ $additionalColumn['name'] }}" />
                @endif
            @endif
        @endforeach
    </div>
</div>
