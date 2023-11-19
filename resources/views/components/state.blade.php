<div x-data="{
    model: $wire.entangle('{{ $attributes->wire('model')->value }}', {{ $attributes->wire('model')->hasModifier('live') ? 'true' : 'false' }}),
    availableStates: $wire.entangle('{{ $attributes->get('available') }}'),
    @if($attributes->wire('formatter')->value)
        formatter: $wire.entangle('{{ $attributes->wire('formatter')->value }}'),
    @else
        formatter: {{ $attributes->get('formatters') }}
    @endif
}">
    @if ($label ?? false)
        <x-label class="mb-1">
            {{ $label }}
        </x-label>
    @endif
    <div class="dropdown-full-w">
        <x-dropdown width="w-full" align="{{ $attributes->get('align', 'right') }}">
            <x-slot:trigger>
                <button
                    wire:loading.attr="disabled"
                    wire:loading.class="!cursor-wait"
                    type="button"
                    class="w-full group inline-flex items-center justify-center gap-x-1 gap-x-2 rounded px-4 py-2 text-sm font-semibold outline-none hover:shadow-sm focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80 dark:bg-slate-700 dark:ring-slate-600 dark:ring-offset-slate-700 dark:hover:bg-slate-700"
                    x-bind:class="'text-' + formatter[1][model] +'-600 bg-' + formatter[1][model] + '-100 dark:text-' + formatter[1][model] + '-400 dark:bg-slate-700'"
                >
                    <span x-text="Array.from(Object.values(availableStates)).find((state) => {return state.name === model})?.label"></span> <x-icon name="chevron-down" class="h-4 w-4" />
                </button>
            </x-slot:trigger>
            <div class="grid grid-cols-1 gap-3 py-2">
                <template x-for="state in availableStates">
                    <div x-on:click="model = state.name" class="flex w-full cursor-pointer items-center">
                        <x-icon x-show="state.name === model" name="check" class="h-4 w-4" />
                        <div x-html="window.formatters.state(state.label, formatter[1][state.name])">
                        </div>
                    </div>
                </template>
            </div>
        </x-dropdown>
    </div>
</div>
