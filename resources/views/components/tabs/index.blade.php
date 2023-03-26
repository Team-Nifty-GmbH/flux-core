<div class="mt-2"
     x-data="{
        tab: $wire.entangle('{{ $attributes->wire('model')->value() }}'),
        tabs: @js($tabs),
    }"
     wire:ignore
>
    <div class="pb-2.5">
        <div class="dark:border-secondary-700 border-b border-gray-200">
            <nav class="soft-scrollbar flex gap-x-8 overflow-x-auto">
                <template x-for="(label, name) in tabs">
                    <button wire:loading.attr="disabled" x-on:click.prevent="tab = name" x-bind:class="{'!border-indigo-500 text-indigo-600' : tab === name}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50" x-text="label" />
                </template>
            </nav>
        </div>
    </div>
</div>
<div class="relative pt-6">
    @if($attributes->has('wire:loading'))
        <x-spinner {{ $attributes->thatStartWith('wire:loading') }} />
    @endif
    {{ $slot ?? '' }}
</div>
