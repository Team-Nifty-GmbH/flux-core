<div
    x-init="printStore.onInit($wire, $refs)"
    x-data="{
        printStore: $store.printStore,
        headerStore: $store.headerStore,
        firstPageHeaderStore: $store.firstPageHeaderStore,
        footerStore: $store.footerStore,
    }"
    class="flex h-[29.7cm] items-center space-x-4"
>
    <div class="h-full w-[300px] overflow-y-auto rounded bg-white p-4 shadow">
        @if ($this->availableClients)
            <x-select.native
                :label="__('Selected Client')"
                x-bind:disabled="printStore.anyEdit || printStore.loading"
                x-on:change="printStore.selectClient($event,$wire,$refs)"
                select="label:name|value:id"
                :options="$availableClients"
            />
            <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
            <x-flux::print.available-elements.order.first-page-header
                :client="$client"
            />
            <x-flux::print.available-elements.header :client="$client" />
            <x-flux::print.available-elements.footer :client="$client" />
        @endif
    </div>
    @if ($this->availableClients)
        <div class="relative">
            <div
                x-cloak
                x-show="printStore.loading"
                class="absolute z-[500] h-full w-full bg-gray-300"
            >
                <div class="h-full w-full animate-pulse bg-gray-100"></div>
            </div>
            <x-flux::a4-page />
        </div>
    @else
        <div
            class="flex h-full w-[21cm] flex-col items-center justify-center rounded bg-white shadow"
        >
            <div class="text-lg text-gray-500">
                {{ __('No clients available. Please create a client first.') }}
            </div>
        </div>
    @endif
    <div class="h-full w-[300px] rounded bg-white p-4 shadow">
        <div
            x-cloak
            x-show="!printStore.anyEdit"
            class="flex h-full flex-col space-y-4"
        >
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditMargin()"
                :text="__('Edit Margin')"
            />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditHeader()"
                :text="__('Edit Header')"
            />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditFirstPageHeader()"
                :text="__('Edit First Page Header')"
            />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditFooter()"
                :text="__('Edit Footer')"
            />
            <div class="flex-1"></div>
            <div
                class="w-full border-b border-gray-300 pb-2 text-[16px] font-light text-gray-600"
            >
                Navigation
            </div>
            <div class="flex justify-between">
                <x-button
                    x-bind:disabled="printStore.loading"
                    href="{{ route('settings',[
                        'setting-entry' => 'settings.print-layouts'
                        ]) }}"
                    :text="__('Settings')"
                />
                <x-button
                    x-bind:disabled="printStore.loading"
                    href="{{ route('dashboard') }}"
                    :text="__('Dashboard')"
                />
            </div>
        </div>
        <div
            x-cloak
            x-show="printStore.anyEdit"
            class="flex h-full flex-col justify-end"
        >
            <x-flux::print.controll-panel.footer />
            <x-flux::print.controll-panel.header />
            <x-flux::print.controll-panel.order.first-page-header />
            <div
                x-cloak
                x-show="!printStore.anyEdiorOpen"
                class="flex items-center justify-between"
            >
                <x-button
                    x-bind:disabled="printStore.loading"
                    x-on:click="printStore.closeEditor($refs)"
                    :text="__('Cancel')"
                />
                <x-button
                    x-bind:disabled="printStore.loading"
                    x-on:click="printStore.submit($wire,$refs)"
                    :text="__('Submit')"
                />
            </div>
        </div>
    </div>
</div>
