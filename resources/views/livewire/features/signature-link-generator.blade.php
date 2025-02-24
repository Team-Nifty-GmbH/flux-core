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
            <x-dropdown>
                <x-slot:action>
                    <x-button x-on:click="show = !show" color="secondary" light class="w-full" icon="document">
                        {{ __('Add Signature') }}
                    </x-button>
                </x-slot:action>
                @foreach($unsignedViews as $unsignedView)
                    <x-dropdown.items
                        wire:click="setPublicLink('{{ $unsignedView }}').then(() => show = false)">
                        {{ __($unsignedView) }}
                    </x-dropdown.items>
                @endforeach
            </x-dropdown>
        </div>
    @endif
    @if($generatedUrls)
        <div class="mt-4">
            @foreach($generatedUrls as $label => $link)
                <x-clipboard :label="__($label)" :text="$link" />
            @endforeach
        </div>
    @endif
</div>
