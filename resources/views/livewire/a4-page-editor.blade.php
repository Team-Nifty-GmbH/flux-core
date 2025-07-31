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
            <div x-cloak x-show="printStore.editHeader">
                <div  class="flex items-center justify-between">
                    <div class=" text-lg text-gray-600">Subject</div>
                    <x-toggle
                        x-on:change="headerStore.toggleElement($refs,'header-subject')"
                        x-bind:value="headerStore.visibleElements.map(e => e.id).includes('header-subject')"
                    />
                </div>
                <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
            </div>
            <div x-show="printStore.editFooter" x-cloak>
                <div class="pb-4 text-lg text-gray-600">Client</div>
                <div class="flex items-center justify-between">
                    <address class="not-italic">
                        <div class="font-semibold">
                            {{ $client->name ?? '' }}
                        </div>
                        <div>
                            {{ $client->ceo ?? '' }}
                        </div>
                        <div>
                            {{ $client->street ?? '' }}
                        </div>
                        <div>
                            {{ trim(($client->postcode ?? '') . ' ' . ($client->city ?? '')) }}
                        </div>
                        <div>
                            {{ $client->phone ?? '' }}
                        </div>
                        <div>
                            <div>
                                {{ $client->vat_id }}
                            </div>
                        </div>
                    </address>
                    <x-toggle
                        x-on:change="footerStore.toggleElement($refs,'footer-client-{{$client->id}}')"
                        x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-client-{{$client->id}}')" />
                </div>
                <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
            </div>
        @endif

            <div x-show="printStore.editFooter" x-cloak>
        <div class="pb-4 text-lg text-gray-600">Bank Connections</div>
        <div class="flex flex-col gap-4">
            @foreach ($this->client?->bankConnections ?? [] as $bankConnection)
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold">
                            {{ $bankConnection->bank_name ?? '' }}
                        </div>
                        <div>
                            {{ $bankConnection->iban ?? '' }}
                        </div>
                        <div>
                            {{ $bankConnection->bic ?? '' }}
                        </div>
                    </div>
                    <x-toggle
                        x-on:change="footerStore.toggleElement($refs,'footer-bank-{{$bankConnection->id}}')"
                        x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-bank-{{$bankConnection->id}}')" />
                </div>
            @endforeach
        </div>
        <div class="mb-4 mt-4 w-full border-t border-gray-300"></div>
        </div>
            @if($this->client->logo_small_url)
        <div x-cloak x-show="printStore.editFooter">
            <div class="pb-4 text-lg text-gray-600">Logo</div>
            <div class="flex items-center justify-between">
                <img class="h-[1.7cm]" src="{{ $this->client->logo_small_url }}" />
                <x-toggle
                x-on:change="footerStore.toggleElement($refs,'footer-logo')"
                x-bind:value="footerStore.visibleElements.map(e => e.id).includes('footer-logo')" />
            </div>
        </div>
                <div x-cloak x-show="printStore.editHeader">
                    <div class="pb-4 text-lg text-gray-600">Logo</div>
                    <div class="flex items-center justify-between">
                        <img class="h-[1.7cm]" src="{{ $this->client->logo_small_url }}" />
                        <x-toggle
                            x-on:change="headerStore.toggleElement($refs,'header-logo')"
                            x-bind:value="headerStore.visibleElements.map(e => e.id).includes('header-logo')"
                        />
                    </div>
                </div>
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
