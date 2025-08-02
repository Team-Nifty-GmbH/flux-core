<div class="flex gap-4" x-data="{ search: null }">
    <x-card
        wire:ignore
        scope="w-auto"
        x-on:folder-tree-select="$wire.showSetting($event.detail)"
    >
        <x-flux::checkbox-tree
            tree="$wire.settings"
            name-attribute="label"
            :with-search="true"
        >
            <div x-init.once="if ({{ ! is_null($setting) }}) { selected = @js($setting); }"></div>
            <x-slot:nodeIcon></x-slot>
        </x-flux::checkbox-tree>
    </x-card>
    <x-card>
        <x-slot:header>
            <div x-text="$wire.setting.path"></div>
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
