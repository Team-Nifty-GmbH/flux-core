<div>
     @if($this->getSignature && is_null($publicLink))
        <x-button class="w-full"
        @click="$wire.setPublicLink"
        label="Create Signature"/>
    @elseif(!is_null($publicLink))
        <x-input
            :label="__('Link')"
            readonly
            type="text"
            x-ref="link"
            x-bind:value="$wire.publicLink">
            <x-slot:append>
                <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                    <x-button
                        x-on:click="$refs.link.select(); document.execCommand('copy');"
                        class="h-full rounded-r-md"
                        icon="clipboard-copy"
                        primary
                        squared/>
                </div>
            </x-slot:append>
            </x-input>
    @else
        <p>{{ __('Signature saved') }}</p>
    @endif
</div>
