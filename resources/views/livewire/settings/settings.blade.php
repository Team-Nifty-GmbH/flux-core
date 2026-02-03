<div
    class="flex flex-col gap-4 lg:flex-row"
    x-data="{ showContent: {{ ! is_null($setting) ? 'true' : 'false' }} }"
>
    <x-card
        wire:ignore
        scope="w-auto"
        x-on:folder-tree-select="$wire.showSetting($event.detail); showContent = true"
        x-show="! showContent"
        x-cloak
        class="lg:!block"
    >
        <x-flux::checkbox-tree
            tree="$wire.settings"
            name-attribute="label"
            :with-search="true"
        >
            <div
                x-init.once="
                    if ({{ ! is_null($setting) }}) {
                        selected = @js($setting)
                    }
                "
            ></div>
            <x-slot:nodeIcon></x-slot>
        </x-flux::checkbox-tree>
    </x-card>
    <x-card x-show="showContent" x-cloak class="lg:!block">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <x-button
                    icon="arrow-left"
                    flat
                    x-on:click="showContent = false"
                    class="lg:!hidden"
                />
                <div x-text="$wire.setting.path"></div>
            </div>
        </x-slot>
        <x-flux::spinner />
        @if ($settingComponent)
            <livewire:is
                :component="$settingComponent"
                :key="$settingComponent"
            />
        @endif
    </x-card>
</div>
