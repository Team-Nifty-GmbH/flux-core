<div class="flex flex-col gap-4"
     wire:init="checkForUpdates()"
     x-data="{
        showOnlyFluxPlugins: true,
        entangledInstalled: $wire.$entangle('installed', true),
        get installed() {
            if (! this.showOnlyFluxPlugins) return $wire.installed;

            return Object.fromEntries(Object.entries($wire.installed).filter(([key, value]) => value.is_flux_plugin));
        }
     }"
>
    @if(resolve_static(\FluxErp\Actions\Plugins\Uninstall::class, 'canPerformAction', [false]))
        <x-dialog id="uninstall">
            <x-checkbox id="delete-data" label="{{ __('Delete all data') }}" />
        </x-dialog>
    @endif
    @if(resolve_static(\FluxErp\Actions\Plugins\Update::class, 'canPerformAction', [false]))
        <x-modal name="update" width="7xl">
            <x-card class="w-full">
                <x-slot:title>
                    <span>{{ __('Update') }}</span>
                    <span x-text="$wire.update.package"></span>
                    <span x-text="$wire.update.version"></span>
                </x-slot:title>
                <div class="prose max-w-full" x-html="$wire.update.readme"></div>
                <x-slot:footer>
                    <div class="flex justify-end gap-1.5 items-center">
                        <x-button flat :label="__('Close')" x-on:click="close()" />
                        <x-button primary :label="__('Update')" spinner="update" wire:click="updatePackages($wire.update.package); close();" />
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    @endif
    <x-modal name="more" width="7xl">
        <x-card class="w-full">
            <div class="prose max-w-full" x-html="$wire.readme"></div>
            <x-slot:footer>
                <div class="flex justify-end gap-1.5 items-center">
                    <x-button flat :label="__('Close')" x-on:click="close()" />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    @if(resolve_static(\FluxErp\Actions\Plugins\Install::class, 'canPerformAction', [false]))
        <x-modal name="install" width="7xl">
            <x-card :title="__('Install packages')" class="w-full">
                <div class="flex flex-col gap-4">
                    <x-flux::features.media.upload-form-object wire:model="file" :multiple="true" accept=".zip,.rar,.7zip">
                        <x-slot:footer>
                            <div x-show="$wire.file.stagedFiles.length > 0" x-cloak x-transition class="flex justify-end">
                                <x-button primary spinner="installUploaded" :label="__('Upload package')" wire:click="installUploaded" wire:flux-confirm.icon.warning="{{ __('wire:confirm.install-uploaded-plugin') }}"/>
                            </div>
                        </x-slot:footer>
                    </x-flux::features.media.upload-form-object>
                    <x-input type="search" wire:model.live.debounce="search" :placeholder="__('Search on packagist.org…')" />
                    <div class="flex flex-col gap-1.5 pt-4">
                        <template x-for="plugin in $wire.searchResult">
                            <div class="flex justify-between gap-4">
                                <img x-bind:src="plugin.url ? new URL(plugin.url).origin + '/favicon.ico' : '{{ route('icons', ['name' => 'archive-box', 'variant' => 'outline'])}}'" alt="Plugin Image" class="w-12 h-12 rounded-lg">
                                <div class="flex-grow">
                                    <div class="flex gap-1.5">
                                        <span x-text="plugin.name"></span>
                                        <x-badge positive :label="__('Flux Plugin')" x-show="plugin.is_flux_plugin" />
                                    </div>
                                    <div class="flex gap-1.5">
                                        <x-badge primary>
                                            <x-icon name="download" class="w-3 h-3 fill-warning-400" />
                                            <span class="font-semibold text-xs" x-text="plugin.downloads + ' ' + '{{ __('Downloads') }}'"></span>
                                        </x-badge>
                                        <x-badge>
                                            <x-icon name="star" class="w-3 h-3 fill-warning-400" />
                                            <span class="font-semibold text-xs" x-text="plugin.favers"></span>
                                        </x-badge>
                                    </div>
                                    <a x-bind:href="plugin.repository" target="_blank" class="text-xs" x-text="plugin.repository"></a>
                                    <div class="text-xs" x-text="plugin.author"></div>
                                </div>
                                <div class="flex-none flex gap-1.5">
                                    <div>
                                        <x-button primary :label="__('More')" x-on:click="$wire.more(plugin.name)" />
                                        <x-button positive :label="__('Install')" x-on:click="$wire.install(plugin.name)" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <x-slot:footer>
                    <div class="flex justify-end gap-1.5 items-center">
                        <x-button flat :label="__('Close')" x-on:click="close()" />
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    @endif
    <div x-show="$wire.offerRefresh" x-transition x-cloak>
        <x-card class="bg-positive-500 text-white gap-4 rounded-xl">
            {{ __('You have to refresh the page to see the changes.') }}
            <x-button x-on:click="window.location.reload(true)" primary :label="__('Refresh')" />
        </x-card>
    </div>
    @error('checkForUpdates')
        <div>
            <x-card class="bg-negative-500 text-white gap-4 rounded-xl">
                <span>{!! $message !!}</span>
            </x-card>
        </div>
    @enderror
    @error('update')
    <div>
        <x-card class="bg-negative-500 text-white gap-4 rounded-xl">
            <span>{!! $message !!}</span>
        </x-card>
    </div>
    @enderror
    <div class="flex justify-between gap-1.5 items-center">
        <x-toggle :label="__('Show only Flux Plugins')" x-model="showOnlyFluxPlugins" />
        <div class="flex gap-1.5">
            @if(resolve_static(\FluxErp\Actions\Plugins\Install::class, 'canPerformAction', [false]))
                <x-button primary :label="__('Install')" x-on:click="$openModal('install')" />
            @endif
            @if(resolve_static(\FluxErp\Actions\Plugins\Update::class, 'canPerformAction', [false]))
                <div x-transition x-show="$wire.outdated === 0">
                    <x-button positive :label="__('Check for Updates')" spinner="checkForUpdates" wire:click="checkForUpdates()" />
                </div>
                <div x-transition x-cloak x-show="$wire.outdated > 0">
                    <x-button positive :label="__('Update all')" spinner="updateAll" wire:click="updateAll()" />
                </div>
            @endif
        </div>
    </div>
    <template x-for="(plugin, key) in installed">
        <x-card>
            <div class="flex justify-between gap-4">
                <div class="flex-none flex gap-1.5 items-center">
                    @if(resolve_static(\FluxErp\Actions\Plugins\ToggleActive::class, 'canPerformAction', [false]))
                        <div x-cloak x-bind:class="! (plugin.can_uninstall && ! plugin.offer_install) && 'invisible'">
                            <x-toggle x-model="entangledInstalled[key].is_active" />
                        </div>
                    @endif
                </div>
                <div class="flex-grow flex gap-1.5" x-bind:class="! plugin.is_active && 'opacity-60'">
                    <img
                        x-bind:src="plugin.homepage ? new URL(plugin.homepage).origin + '/favicon.ico' : '{{ route('icons', ['name' => 'archive-box', 'variant' => 'outline'])}}'"
                        alt="Plugin Image"
                        class="w-12 h-12 rounded-lg"
                        x-on:error="$el.src = '{{ route('icons', ['name' => 'archive-box', 'variant' => 'outline'])}}'"
                    >
                    <div class="flex flex-col">
                        <div class="flex gap-1.5">
                            <span class="font-semibold" x-text="plugin.name"></span>
                            <div x-cloak x-show="plugin.is_flux_plugin && plugin.name !== 'team-nifty-gmbh/flux-erp'">
                                <x-badge positive :label="__('Flux Plugin')" />
                            </div>
                        </div>
                        <div class="font-semibold text-xs" x-text="'{{ __('Version') }}' + ': ' + plugin.version"></div>
                        <div class="text-xs" x-text="plugin.description"></div>
                        <a x-bind:href="plugin.homepage" x-text="plugin.homepage" target="_blank" class="text-xs"></a>
                        <a x-bind:href="plugin.support?.source" x-text="plugin.support?.source" target="_blank" class="text-xs"></a>
                        <div class="text-xs" x-text="plugin.author"></div>
                    </div>
                </div>
                <div>
                    <div class="flex-none flex gap-1.5">
                        <x-button primary :label="__('More')" wire:click="more(key)" />
                        @if(resolve_static(\FluxErp\Actions\Plugins\Install::class, 'canPerformAction', [false]))
                            <div x-cloak x-show="plugin.offer_install">
                                <x-button positive :label="__('Install')" wire:click="install(key, $promptValue('delete-data'))" />
                            </div>
                        @endif
                        @if(resolve_static(\FluxErp\Actions\Plugins\Uninstall::class, 'canPerformAction', [false]))
                            <div x-cloak x-show="plugin.can_uninstall">
                                <x-button negative :label="__('Uninstall')" wire:click="uninstall(key, $promptValue('delete-data'))" wire:flux-confirm.icon.error.id.uninstall="{{ __('wire:confirm.uninstall-plugin') }}" />
                            </div>
                        @endif
                        @if(resolve_static(\FluxErp\Actions\Plugins\Update::class, 'canPerformAction', [false]))
                            <div x-cloak x-show="plugin.latest">
                                <x-button positive :label="__('Update')" wire:click="showChangeLog(key, plugin.latest)">
                                    <x-slot:label>
                                        <span>{{ __('Update to') }}</span><span x-text="plugin.latest" />
                                    </x-slot:label>
                                </x-button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </template>
</div>
