<div
    x-data="{
        log: {}
    }"
    x-on:data-table-row-clicked="$wire.loadLog($event.detail.id).then(
        result => {
            log = result;
            $modalOpen('show-log-modal');
        }
    )">
    <x-modal id="show-log-modal">
        <div class="py-3 px-6 w-full flex justify-between">
            <span x-text="formatters.datetime(log?.created_at)"></span>
            <span class="uppercase text-xl"
                  x-text="log?.level + ' | ' + log?.level_name"></span>
        </div>
        <div class="py-3 px-6">
            <div class="text-gray-600 uppercase text-sm leading-normal"
                 x-text="log.message">{{ __('Message') }}</div>
        </div>
        <div class="py-3 px-6">
            <div class="text-gray-600 uppercase text-sm leading-normal">{{ __('Extra') }}</div>
            <div class="bg-black font-mono text-white overflow-scroll rounded-md p-1" x-text="log?.extra">
            </div>
        </div>
        <div class="py-3 px-6 relative overflow-hidden">
            <div class="text-gray-600 uppercase text-sm leading-normal">
                {{ __('Formatted') }}
            </div>
            <div class="bg-black font-mono text-white overflow-auto rounded-md p-1" x-text="log?.formatted">
            </div>
        </div>
        <div class="py-3 px-6 relative overflow-hidden">
            <div class="text-gray-600 uppercase text-sm leading-normal">
                {{ __('Context') }}
            </div>
            <div class="bg-black font-mono text-white overflow-auto rounded-md p-1 whitespace-pre"
                 x-text="JSON.stringify(JSON.parse(log?.context ?? '{}'), null, 4)">
            </div>
        </div>
        <x-slot:footer>
            <x-button color="secondary" light :text="__('Close')" x-on:click="$modalClose('show-log-modal')" class="mr-2"/>
        </x-slot:footer>
    </x-modal>
    <div class="mb-6 sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold">{{ __('Logs') }}</h1>
        </div>
    </div>
    <div wire:ignore>
        <livewire:data-tables.log-list />
    </div>
</div>
