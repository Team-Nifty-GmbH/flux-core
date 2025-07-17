<div
    x-data="printEditorMain()"
    class="flex h-[29.7cm] items-center space-x-4"
>
    <div class="h-full w-[300px] rounded bg-white p-4 shadow">
        @if ($this->availableClients)
            <x-select.native
                label="Selected Client"
                x-bind:disabled="anyEdit"
                x-on:change="selectClient"
                select="label:name|value:id"
                :options="$availableClients"
            />
            <div class="mb-4 mt-4 w-full border-t border-gray-400"></div>
            <div x-show="editFooter" x-cloak>
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
                    <x-toggle />
                </div>
                <div class="mb-4 mt-4 w-full border-t border-gray-400"></div>
            </div>
        @endif

            <div x-show="editFooter" x-cloak>
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
                    <x-toggle />
                </div>
            @endforeach
        </div>
        <div class="mb-4 mt-4 w-full border-t border-gray-400"></div>
        </div>
        <div x-cloak x-show="editFooter">
        <div class="pb-4 text-lg text-gray-600">Logo</div>
        <div class="flex items-center justify-between">
            <img class="h-[1.7cm]" src="{{ $this->client->logo_small_url }}" />
            <x-toggle />
        </div>
    </div>
    </div>
    @if ($this->availableClients)
        <x-flux::a4-page />
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
        <div x-cloak x-show="!anyEdit" class="flex flex-col space-y-4">
            <x-button x-on:click="toggleEditMargin" text="Edit Margin" />
            <x-button x-on:click="toggleEditHeader" text="Edit Header" />
            <x-button x-on:click="toggleEditFooter" text="Edit Footer" />
        </div>
        <div
            x-cloak
            x-show="anyEdit"
            class="flex h-full flex-col justify-end"
        >
            <div class="flex items-center justify-between">
                <x-button x-on:click="closeEditor" text="Cancel" />
                <x-button text="Save" />
            </div>
        </div>
    </div>
</div>
