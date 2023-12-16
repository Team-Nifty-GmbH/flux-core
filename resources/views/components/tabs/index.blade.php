<div class="mt-2"
     x-data="{
        tab: $wire.entangle('{{ $attributes->wire('model')->value() }}', true),
        tabButtonClicked(tabButton) {
            this.tabSelected = this.tab = tabButton.dataset.tabName;
        },
    }"
     wire:ignore
>
    <div class="pb-2.5">
        <div class="dark:border-secondary-700 border-b border-gray-200">
            <nav class="soft-scrollbar flex overflow-x-auto" x-ref="tabButtons">
                @foreach($tabs as $tabButtons)
                    {{ $tabButtons }}
                @endforeach
            </nav>
        </div>
    </div>
</div>
<div {{ $attributes->whereDoesntStartWith(['wire', 'tabs'])->merge(['class' => 'relative pt-6']) }}>
    {{ $prepend ?? '' }}
    @if($attributes->has('wire:loading'))
        <x-spinner {{ $attributes->thatStartWith('wire:loading') }} />
    @endif
    <div class="w-full">
        @if($slot->isNotEmpty())
            {{ $slot }}
        @elseif($tabs[$this->{$attributes->wire('model')->value()}]?->isLivewireComponent)
            <livewire:dynamic-component wire:model="{{ $tabs[$this->{$attributes->wire('model')->value()}]?->wireModel }}" :is="$this->{$attributes->wire('model')->value()}" wire:key="{{ uniqid() }}"/>
        @else
            <x-dynamic-component :component="$this->{$attributes->wire('model')->value()}" />
        @endif
    </div>
    {{ $append ?? '' }}
</div>

