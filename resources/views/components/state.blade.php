<div
    x-data="{
    model: $wire.$entangle('{{ $attributes->wire("model")->value }}', {{ $attributes->wire("model")->hasModifier("live") ? "true" : "false" }}),
    availableStates: $wire.$entangle('{{ $attributes->get("available") }}'),
    @if ($attributes->wire("formatter")->value)
        formatter: $wire.$entangle('{{ $attributes->wire("formatter")->value }}'),
    @else
        formatter: {{ $attributes->get("formatters") }}
    @endif
}"
>
    @if ($label ?? false)
        <div class="mb-1">
            <x-label>
                {{ $label }}
            </x-label>
        </div>
    @endif

    <div class="dropdown-full-w" {{ $attributes->whereStartsWith("x-bind") }}>
        <x-dropdown
            position="{{ $attributes->get('align', 'bottom') }}"
            scope="state"
        >
            <x-slot:action>
                <button
                    x-on:click="show = !show"
                    wire:loading.attr="disabled"
                    wire:loading.class="!cursor-wait"
                    type="button"
                    class="group inline-flex w-full items-center justify-center gap-x-2 rounded px-4 py-2 text-sm font-semibold outline-none hover:shadow-sm focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80 dark:bg-slate-700 dark:ring-slate-600 dark:ring-offset-slate-700 dark:hover:bg-slate-700"
                    x-bind:class="
                        'text-' +
                            formatter[1][model] +
                            '-600 bg-' +
                            formatter[1][model] +
                            '-100 dark:text-' +
                            formatter[1][model] +
                            '-400 dark:bg-slate-700'
                    "
                >
                    <span
                        x-text="
                            Array.from(Object.values(availableStates)).find((state) => {
                                return state.name === model
                            })?.label
                        "
                    ></span>
                    <x-icon name="chevron-down" class="h-4 w-4" />
                </button>
            </x-slot>
            <template x-for="state in availableStates">
                <x-dropdown.items x-on:click="model = state.name; show = false">
                    <div
                        x-html="window.formatters.state(state.label, formatter[1][state.name])"
                    ></div>
                </x-dropdown.items>
            </template>
        </x-dropdown>
    </div>
</div>
