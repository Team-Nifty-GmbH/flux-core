<div x-data="{
    setting: $wire.entangle('setting').defer,
    calculateBackground() {
        return `linear-gradient(${this.setting.settings.nav.background.angle}deg, ${this.setting.settings.nav.background.start}, ${this.setting.settings.nav.background.end})`;
    }
}">
    <div class="py-8 sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold">{{ __('Customer Portal') }}</h1>
            <div class="mt-2 text-sm text-gray-300">{{__('Here you can manage all customer portal settings...')}}</div>
        </div>
    </div>
    <div class="grid-cols-3 gap-8 pb-8 md:grid">
        <x-card :title="__('General')">
            <x-select :label="__('Dashboard module')" :options="$modules" wire:model.defer="setting.settings.dashboard_module"/>
            <x-select :label="__('Calendars')" wire:model.defer="setting.settings.calendars" :options="$calendars" :multiselect="true" option-label="name" option-value="id" />
        </x-card>
        <x-card :title="__('Navigation styling')">
            <div class="grid grid-cols-2 gap-4">
                <div class="grid grid-cols-1 gap-2">
                    <div>
                        <x-label :label="__('Start color')" />
                        <input class="w-full" type="color" x-on:change="calculateBackground()" wire:model.defer="setting.settings.nav.background.start" />
                    </div>
                    <div>
                        <x-label><span x-text="'{{ __('Angle') }} ' + setting.settings.nav.background.angle + '°'"></span></x-label>
                        <input class="w-full" type="range" x-on:change="calculateBackground()" min="0" max="360" wire:model="setting.settings.nav.background.angle" />
                    </div>
                    <div>
                        <x-label :label="__('End color')" />
                        <input class="w-full" type="color" x-on:change="calculateBackground()" wire:model.defer="setting.settings.nav.background.end" />
                    </div>
                </div>
                <div class="h-full w-full" x-bind:style="{backgroundImage: calculateBackground()}">
                </div>
            </div>
            <div class="m-2 w-full border"></div>
            <x-label :label="__('Active menu item')" />
            <input class="w-full" type="color" wire:model.defer="setting.settings.nav.active_item" />
            <x-label :label="__('Hover menu item')" />
            <input class="w-full" type="color" wire:model.defer="setting.settings.nav.hover_item" />
            <x-inputs.number min="12" max="48" step="2" :label="__('Icon size')" wire:model.defer="setting.settings.nav.icon_size" />
        </x-card>
        <x-card :title="__('Append links')">
            <div class="space-y-5">
                    <template x-for="(link, index) in setting.settings.nav.append_links" :key="index">
                        <div class="flex w-full items-center space-x-3">
                            <div>
                                <x-label>{{ __('Target blank') }}</x-label>
                                <div class="flex items-center">
                                    <x-checkbox x-model="link.target_blank" />
                                </div>
                            </div>
                            <x-input x-model="link.label" :label="__('Label')"/>
                            <x-input x-model="link.icon" :label="__('Icon')"/>
                            <x-input x-model="link.uri" :label="__('URL')" placeholder="your-website.com" />
                            <div class="ml-1 flex h-full items-center sm:col-span-1">
                                <x-button.circle negative icon="trash" x-on:click="setting.settings.nav.appendLinks.splice(index,1)" />
                            </div>
                        </div>
                    </template>
                <div class="sm:col-span-6">
                    <x-button.circle class="mr-2" primary icon="plus" x-on:click="setting.settings.nav.append_links.push({children: []})" />
                </div>
            </div>
        </x-card>
    </div>
    <x-card :title="__('Custom CSS')">
        <x-textarea x-model="setting.settings.custom_css"></x-textarea>
    </x-card>

    <div class="flex justify-end space-x-5 pt-5">
        <x-button :label="__('Cancel')" :href="route('settings.settings.clients')"/>
        <x-button primary :label="__('Save')" wire:click="save"/>
    </div>
</div>
