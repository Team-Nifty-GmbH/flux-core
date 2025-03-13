<div class="flex flex-col gap-1 dark:text-gray-50">
    <div class="flex w-full justify-end">
        <x-button.circle
            xs
            icon="x-mark"
            color="red"
            wire:click="remove({{ $cartItem->id }})"
        />
    </div>
    <div class="flex justify-start gap-2">
        <x-avatar
            squared
            :image="($cartItem->product->coverMedia ?? $cartItem->product->parent?->coverMedia)?->getUrl('thumb')"
            class="h-12 w-12 rounded-lg object-cover"
        />
        <div>{{ $cartItem->name }}</div>
    </div>
    <x-input
        type="number"
        min="0"
        step="1"
        :value="$cartItem->amount"
        x-on:input="($event) => $wire.updateAmount({{ $cartItem->id }}, parseFloat($event.target.value))"
    />
    <div class="flex flex-col text-right">
        <div class="font-semibold">
            {{ Number::currency(number: $cartItem->total, locale: app()->getLocale()) }}
            *
        </div>
        <div class="text-secondary-400">
            {{ Number::currency(number: $cartItem->price, locale: app()->getLocale()) }}
            / {{ __("Piece") }} *
        </div>
    </div>
</div>
