<div
    x-init="printStore.onInit($wire,$refs)"
    x-data="{
        printStore: $store.printStore,
        headerStore: $store.headerStore,
        firstPageHeaderStore: $store.firstPageHeaderStore,
        footerStore: $store.footerStore,
    }"
    class="flex h-[29.7cm] items-center space-x-4"
>
    <div class="h-full w-[300px] rounded bg-white p-4 shadow">
        @if ($this->availableClients)
            <x-select.native
                label="Selected Client"
                x-bind:disabled="printStore.anyEdit || printStore.loading"
                x-on:change="printStore.selectClient($event,$wire,$refs)"
                select="label:name|value:id"
                :options="$availableClients"
            />
            <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
            <x-flux::print.available-elements.order.first-page-header :client="$client" :model="$model"/>
            <x-flux::print.available-elements.header :client="$client"/>
            <x-flux::print.available-elements.footer :client="$client"/>
        @endif
    </div>
    @if ($this->availableClients)
        <div class="relative">
            <div x-cloak x-show="printStore.loading" class="absolute w-full h-full z-[500] bg-gray-300">
                <div class="animate-pulse w-full h-full bg-gray-100"></div>
            </div>
            <x-flux::a4-page />
        </div>
    @else
        <div
            class="flex h-full w-[21cm] flex-col items-center justify-center rounded bg-white shadow"
        >
            <div class="text-lg text-gray-500">
                No clients available. Please create a client first.
            </div>
        </div>
    @endif
    <div class="h-full w-[300px] rounded bg-white p-4 shadow">
        <div x-cloak x-show="!printStore.anyEdit" class="flex flex-col space-y-4">
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditMargin()" text="Edit Margin" />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditHeader()" text="Edit Header" />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditFirstPageHeader()" text="Edit First Page Header" />
            <x-button
                x-bind:disabled="printStore.loading"
                x-on:click="printStore.toggleEditFooter()" text="Edit Footer" />
        </div>
        <div
            x-cloak
            x-show="printStore.anyEdit"
            class="flex h-full flex-col justify-end"
        >
            <div class="flex items-center justify-between">
                <x-button
                    x-bind:disabled="printStore.loading"
                    x-on:click="printStore.closeEditor($refs)" text="Cancel" />
                <x-button
                    x-bind:disabled="printStore.loading"
                    x-on:click="printStore.submit($wire)" text="Submit" />
            </div>
        </div>
    </div>
</div>
