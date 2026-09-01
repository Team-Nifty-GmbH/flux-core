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
            this._unhookCommit = Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    this.$refs.tabButtons
                        ?.querySelectorAll('.animate-pulse')
                        .forEach(btn => btn.classList.remove('animate-pulse'))
                    this.loadingTab = null
                })
            })
        },
        destroy() {
            this._unhookCommit?.()
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

    @php
        $tabProperty = $attributes->wire('model')->value();
        $activeTab = $this->{$tabProperty};
        $activeTabButton = $tabs[$activeTab] ?? null;
        $boundModelKey = $activeTabButton?->wireModel;
        $boundModelValue = $boundModelKey ? data_get($this, $boundModelKey) : null;

        if (! is_scalar($boundModelValue)) {
            $boundModelValue = data_get($boundModelValue, 'id');
        }
    @endphp

    <div class="min-w-0 w-full">
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @elseif ($activeTabButton?->isLivewireComponent)
            <livewire:dynamic-component
                wire:model="{{ $boundModelKey }}"
                :is="$activeTab"
                wire:key="tab-{{ $tabProperty }}-{{ $activeTab }}{{ is_null($boundModelValue) ? '' : '-' . $boundModelValue }}"
            />
        @else
            <x-dynamic-component :component="$activeTab" />
        @endif
    </div>
    {{ $append ?? '' }}
</div>
