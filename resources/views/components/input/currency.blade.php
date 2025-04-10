<div
    x-data="{
        input: {{ $attributes->wire('model')->value ? '$wire.entangle(\'' . $attributes->wire('model')->value . '\')' : ($attributes->whereStartsWith('x-model')->first() ?: 'null') }},
        thousands: '{{ $thousands }}',
        decimal: '{{ $decimal }}',
        precision: {{ $precision }},
        maskedValue: null,
        mask(value) {
            return value?.toString().replace('.', this.decimal)
        },
        emitInput(value) {
            this.input = value
                .replaceAll(this.thousands, '')
                .replace(this.decimal, '.')
        },
    }"
    x-init="
        $watch('input', (value) => {
            this.maskedValue = mask(value)
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
        x-model="maskedValue"
        x-mask:dynamic="$money($input, decimal, thousands, precision)"
        x-init="maskedValue = mask(input); emitInput(maskedValue);"
        x-on:input="emitInput($event.target.value)"
    />
</div>
