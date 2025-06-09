<div
    wire:ignore
    wire:loading.delay.longer
    {{ $attributes->merge(['class' => 'min-h-8 backdrop-blur-sm bg-white/30 absolute right-0 top-0 left-0 bottom-0', 'style' => 'z-index: 1']) }}
>
    <div
        class="absolute bottom-0 left-0 right-0 top-0 flex items-center justify-center"
    >
        <div class="text-center">
            <div role="status">
                <x-flux::spinner-svg />
                <span class="sr-only">{{ __('Loading...') }}</span>
            </div>
        </div>
    </div>
</div>
