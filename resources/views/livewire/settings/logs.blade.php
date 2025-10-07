<div
    x-data="{
        log: {},
    }"
    x-on:data-table-row-clicked="
        $wire.loadLog($event.detail.record.id).then((result) => {
            log = result
            $modalOpen('show-log-modal')
        })
    "
>
    <x-modal id="show-log-modal">
        <div class="flex w-full justify-between px-6 py-3">
            <span x-text="formatters.datetime(log?.created_at)"></span>
            <span
                class="text-xl uppercase"
                x-text="log?.level + ' | ' + log?.level_name"
            ></span>
        </div>
        <div class="px-6 py-3">
            <div
                class="text-sm uppercase leading-normal text-gray-600"
                x-text="log.message"
            >
                {{ __('Message') }}
            </div>
        </div>
        <div class="px-6 py-3">
            <div class="text-sm uppercase leading-normal text-gray-600">
                {{ __('Extra') }}
            </div>
            <div
                class="overflow-scroll rounded-md bg-black p-1 font-mono text-white"
                x-text="log?.extra"
            ></div>
        </div>
        <div class="relative overflow-hidden px-6 py-3">
            <div class="text-sm uppercase leading-normal text-gray-600">
                {{ __('Formatted') }}
            </div>
            <div
                class="overflow-auto rounded-md bg-black p-1 font-mono text-white"
                x-text="log?.formatted"
            ></div>
        </div>
        <div class="relative overflow-hidden px-6 py-3">
            <div class="text-sm uppercase leading-normal text-gray-600">
                {{ __('Context') }}
            </div>
            <div
                class="overflow-auto whitespace-pre rounded-md bg-black p-1 font-mono text-white"
                x-text="JSON.stringify(JSON.parse(log?.context ?? '{}'), null, 4)"
            ></div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                :text="__('Close')"
                x-on:click="$modalClose('show-log-modal')"
                class="mr-2"
            />
        </x-slot>
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
