<div class="mt-2"
     x-data="{
         init() {
             $nextTick(() => {
                    this.tabRepositionMarker(this.$refs.tabButtons.querySelector('[data-tab-name=' + CSS.escape(this.tab) + ']'));
                    this.$refs.tabMarker.classList.remove('hidden');
             })
         },
        tab: $wire.entangle('{{ $attributes->wire('model')->value() }}', true),
        tabs: @js($tabs),
        tabRepositionMarker(tabButton) {
            this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
            this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
            this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
        },
        tabButtonClicked(tabButton) {
            this.tabSelected = this.tab = tabButton.dataset.tabName;
            this.tabRepositionMarker(tabButton);
        },
    }"
     wire:ignore
>
    <div class="pb-2.5">
        <div class="dark:border-secondary-700 border-b border-gray-200">
            <nav class="soft-scrollbar flex gap-x-8 overflow-x-auto" x-ref="tabButtons">
                <template x-for="(label, name) in tabs">
                    <button
                        {{ $attributes->whereStartsWith('x-') }}
                        wire:loading.attr="disabled"
                        x-on:click.prevent="tabButtonClicked($el)"
                        x-bind:data-tab-name="name"
                        x-bind:class="{
                          'text-indigo-600': tab === name,
                          '!cursor-not-allowed': $el.hasAttribute('disabled')
                        }"
                        class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-sm
                            font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50"
                        x-text="label"
                    />
                </template>
                <div x-ref="tabMarker" class="absolute left-0 w-1/2 h-0.5 duration-300 ease-out hidden" x-cloak>
                    <div class="w-full h-0.5 absolute bottom-0 bg-primary-600 rounded-md shadow-sm">
                    </div>
                </div>
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
