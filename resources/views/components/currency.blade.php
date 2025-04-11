<div
    x-data="{
        input: {{ $attributes->wire('model')->value ? '$wire.$entangle(\'' . $attributes->wire('model')->value . '\')' : ($attributes->whereStartsWith('x-model')->first() ?: 'null') }},
        thousands: '{{ $thousands }}',
        decimal: '{{ $decimal }}',
        maskedValue: null,
        mask(value) {
            return value?.toString().replace('.', this.decimal)
        },
        normalizeValue(value) {
            return parseFloat(
                value?.toString().replace(/\./g, '').replace(',', '.'),
            )
        },
        emitInput(event) {
            this.input = event.target.value
                ?.replaceAll(this.thousands, '')
                .replace(this.decimal, '.')
        },
    }"
    x-init.once="
        maskedValue = mask(input)
        $nextTick(() => $refs.input.dispatchEvent(new Event('input')))

        $watch('input', (value) => {
            if (parseFloat(value) !== normalizeValue(maskedValue)) {
                maskedValue = mask(value)
                $nextTick(() => $refs.input.dispatchEvent(new Event('input')))
            }
        })
    "
>
    <x-input
        :label="$label"
        :hint="$hint"
        :icon="$icon"
        :clearable="$clearable"
        :invalidate="$invalidate"
        :position="$position"
        :prefix="$prefix"
        :suffix="$suffix"
        :disabled="$attributes->get('disabled')"
        :readonly="$attributes->get('readonly')"
        {{
    $attributes
        ->merge([
            'x-ref' => 'input',
            'x-model' => 'maskedValue',
            'x-mask:dynamic' => '$money($input, decimal, thousands, ' . $precision . ')',
            'x-on:input' => 'emitInput($event)',
            'x-on:blur' => $attributes->wire('model')->hasModifier('blur') ? '$wire.$refresh()' : $attributes->get('x-on:blur'),
            'x-on:change' => $attributes->wire('model')->hasModifier('change') || $attributes->wire('model')->hasModifier('lazy') ? '$wire.$refresh()' : $attributes->get('x-on:change'),
        ])
        ->filter(fn (?string $value, string $key) => ! str_starts_with($key, 'wire:model'))
}}
    />
</div>
