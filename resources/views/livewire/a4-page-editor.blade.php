<div x-data="printEditorMain()"
     class="flex h-[29.7cm] items-center space-x-4">
    <div class="h-full w-[300px] rounded bg-white shadow p-4">
        @if($this->availableClients)
            <x-select.native
                            label="Selected Client"
                            x-bind:disabled="anyEdit"
                            x-on:change="async () => {
                                await $wire.set('selectedClientId', $event.target.value);
                            }"
                            select="label:name|value:id"
                            :options="$availableClients"/>
            <div class="w-full border-t border-gray-400 mt-4 mb-4"></div>
            <div class="text-lg text-gray-600 pb-4">Client</div>
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
            <div class="w-full border-t border-gray-400 mt-4 mb-4"></div>
        @endif
        <div class="text-lg text-gray-600 pb-4">Bank Connections</div>
        <div class="flex flex-col gap-4">
        @foreach($this->client?->bankConnections ?? [] as $bankConnection)
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
            <div class="w-full border-t border-gray-400 mt-4 mb-4"></div>
            <div class="text-lg text-gray-600 pb-4">Logo</div>
            <div class="flex justify-between items-center">
                <img class="h-[1.7cm]" src="{{$this->client->logo_small_url}}" />
                <x-toggle />
            </div>
    </div>
        @if($this->availableClients)
            <x-flux::a4-page/>
        @else
            <div class="bg-white shadow rounded h-full w-[21cm] flex flex-col justify-center items-center">
                <div class="text-gray-500 text-lg">
                    No clients available. Please create a client first.
                </div>
            </div>
       @endif
    <div
        class="h-full w-[300px] rounded p-4 bg-white shadow">
        <div
            x-cloak
            x-show="!anyEdit"
            class="flex flex-col space-y-4">
            <x-button
                x-on:click="toggleEditMargin"
                text="Edit Margin"/>
            <x-button
                x-on:click="toggleEditHeader"
                text="Edit Header"/>
            <x-button
                x-on:click="toggleEditFooter"
                text="Edit Footer"/>
        </div>
        <div
            x-cloak
            x-show="anyEdit"
            class="h-full flex flex-col justify-end">
            <div class="flex items-center justify-between">
                <x-button
                    x-on:click="closeEditor"
                    text="Cancel"
                />
                <x-button
                    text="Save"
                />
            </div>
        </div>
    </div>
</div>
