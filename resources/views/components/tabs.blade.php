@props([
    'tabs' => [],
])
<div
    class="mt-2"
    x-data="{
        loadingTab: null,
        get tab() { return $wire.{{ $attributes->wire('model')->value() }} },
        set tab(value) { $wire.$set('{{ $attributes->wire('model')->value() }}', value) },
        tabButtonClicked(tabButton) {
            this.$refs.tabButtons
                .querySelectorAll('[data-tab-name]')
                .forEach(btn => btn.classList.remove('animate-pulse'))
            tabButton.classList.add('animate-pulse')
            this.loadingTab = tabButton.dataset.tabName
            this.tabSelected = this.tab = tabButton.dataset.tabName
        },
        init() {
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    this.$refs.tabButtons
                        .querySelectorAll('.animate-pulse')
                        .forEach(btn => btn.classList.remove('animate-pulse'))
                    this.loadingTab = null
                })
            })
        },
    }"
    wire:ignore
>
    <div class="pb-2.5">
        <div class="dark:border-secondary-700 border-b border-gray-200">
            <nav
                class="soft-scrollbar flex items-center overflow-x-auto"
                x-ref="tabButtons"
                wire:loading.class="pointer-events-none"
            >
                @foreach ($tabs as $tabButton)
                    {{ $tabButton }}
                @endforeach
            </nav>
        </div>
    </div>
</div>
<div
    {{ $attributes->whereDoesntStartWith(['wire', 'tabs'])->merge(['class' => 'relative pt-6 grow flex flex-col xl:flex-row']) }}
>
    {{ $prepend ?? '' }}

    <div class="min-w-0 w-full">
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @elseif ($tabs[$this->{$attributes->wire('model')->value()}]?->isLivewireComponent)
            <livewire:dynamic-component
                wire:model="{{ $tabs[$this->{$attributes->wire('model')->value()}]?->wireModel }}"
                :is="$this->{$attributes->wire('model')->value()}"
                wire:key="tab-{{ $attributes->wire('model')->value() }}-{{ $this->{$attributes->wire('model')->value()} }}"
            />
        @else
            <x-dynamic-component
                :component="$this->{$attributes->wire('model')->value()}"
            />
        @endif
    </div>
    {{ $append ?? '' }}
</div>
