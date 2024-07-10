<div>
    @if(!empty($signed_urls))
        <div class="mb-4 flex flex-col">
            <p>{{__('Signed Documents')}}:</p>
            @foreach($signed_urls as $url)
                <p class="mt-2">{{__($url)}}</p>
            @endforeach
        </div>
    @endif
    @if(!empty($unsigned_documents))
        <div class="dropdown-full-w">
            <x-dropdown width="w-full">
                <x-slot name="trigger">
                    <x-button class="w-full" icon="document">
                        {{ __('Add Signature') }}
                    </x-button>
                </x-slot>
                @foreach($unsigned_documents as $doc)
                    <x-dropdown.item
                        wire:click="setPublicLink('{{$doc}}')">
                        {{ __($doc) }}
                    </x-dropdown.item>
                @endforeach
            </x-dropdown>
        </div>
    @endif
    @if(!empty($generated_urls))
        <div class="mt-4">
            @foreach($generated_urls as $label => $link)
                <x-input
                    class="mb-2"
                    label="{{__($label)}}"
                    readonly
                    value="{{$link}}"
                    type="text"
                    x-ref="link"
                >
                    <x-slot:append>
                        <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                            <x-button
                                x-on:click="$refs.link.select(); document.execCommand('copy');"
                                class="h-full rounded-r-md"
                                icon="clipboard-copy"
                                primary
                                squared
                            />
                        </div>
                    </x-slot:append>
                </x-input>
            @endforeach
        </div>
    @endif
</div>
