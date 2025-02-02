<div class="flex gap-4" x-data="{search: null}">
    <x-card wire:ignore card-classes="!w-auto" x-on:folder-tree-select="$wire.showSetting($event.detail)">
        <x-flux::checkbox-tree
            tree="$wire.settings"
            name-attribute="label"
            :with-search="true"
        >
            <x-slot:nodeIcon>
            </x-slot:nodeIcon>
        </x-flux::checkbox-tree>
    </x-card>
    <x-card>
        <x-slot:title>
            <div x-text="$wire.setting.path" />
        </x-slot:title>
        <x-flux::spinner />
        @if($settingComponent = data_get($setting, 'component'))
            <livewire:is
                :component="$settingComponent"
                :key="$settingComponent"
            />
        @endif
    </x-card>

</div>
