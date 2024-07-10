<div>
    @if($signedUrls)
        <div class="mb-4 flex flex-col">
            <p>{{__('Signed Documents')}}:</p>
            @foreach($signedUrls as $url)
                <p class="mt-2">{{__($url)}}</p>
            @endforeach
        </div>
    @endif
    @if($unsignedDocuments)
        <div class="dropdown-full-w">
            <x-dropdown width="w-full">
                <x-slot name="trigger">
                    <x-button class="w-full" icon="document">
                        {{ __('Add Signature') }}
                    </x-button>
                </x-slot>
                @foreach($unsignedDocuments as $doc)
                    <x-dropdown.item
                        wire:click="setPublicLink('{{$doc}}')">
                        {{ __($doc) }}
                    </x-dropdown.item>
                @endforeach
            </x-dropdown>
        </div>
    @endif
    @if(!empty($generatedUrls))
        <div class="mt-4">
            @foreach($generatedUrls as $label => $link)
                <x-input
                    class="mb-2"
                    label="{{__($label)}}"
                    readonly
                    value="{{$link}}"
                    type="text"
                    x-ref="link{{implode('', array_map('ucfirst', explode('-', $label)))}}"
                >
                    <x-slot:append>
                        <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                            <x-button
                                x-on:click="$refs.link{{implode('', array_map('ucfirst', explode('-', $label)))}}.select(); document.execCommand('copy');"
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
