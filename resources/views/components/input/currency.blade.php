<div
    x-data="{
        input: @entangle($attributes->wire('model')),
        thousands: '{{ $thousands }}',
        decimal: '{{ $decimal }}',
        precision: {{ $precision }},
        maskedValue: null,
        mask(value) {
            return value.replace('.', this.decimal)
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
        x-init="maskedValue = mask(input)"
        x-on:blur="emitInput($event.target.value)"
    />
</div>
