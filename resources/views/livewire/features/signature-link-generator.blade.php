<div class="text-sm">
    @if($signedViews)
        <div class="mb-4 flex flex-col">
            <p>{{ __('Signed Documents') }}:</p>
            @foreach($signedViews as $signedView)
                <p class="mt-2">{{ __($signedView) }}</p>
            @endforeach
        </div>
    @endif
    @if($unsignedViews)
        <div class="dropdown-full-w">
            <x-dropdown width="w-full">
                <x-slot name="trigger">
                    <x-button color="secondary" light class="w-full" icon="document">
                        {{ __('Add Signature') }}
                    </x-button>
                </x-slot>
                @foreach($unsignedViews as $unsignedView)
                    <x-dropdown.items
                        wire:click="setPublicLink('{{ $unsignedView }}')">
                        {{ __($unsignedView) }}
                    </x-dropdown.items>
                @endforeach
            </x-dropdown>
        </div>
    @endif
    @if($generatedUrls)
        <div class="mt-4">
            @foreach($generatedUrls as $label => $link)
                <x-input
                    class="mb-2"
                    :text="__($label)"
                    readonly
                    value="{{ $link }}"
                    type="text"
                    x-ref="link{{ implode('', array_map('ucfirst', explode('-', $label))) }}"
                >
                    <x-slot:append>
                        <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                            <x-button
                                x-on:click="$refs.link{{ implode('', array_map('ucfirst', explode('-', $label))) }}.select(); document.execCommand('copy');"
                                class="h-full rounded-r-md"
                                icon="clipboard-document"
                                color="indigo"
                                squared
                            />
                        </div>
                    </x-slot:append>
                </x-input>
            @endforeach
        </div>
    @endif
</div>
