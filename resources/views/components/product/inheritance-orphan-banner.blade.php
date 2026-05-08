@if($visible)
    <x-alert color="amber">
        <div class="space-y-3">
            <div class="font-semibold">
                {{ __('Dieses Produkt hatte Varianten, aber keine aktive Variante mehr.') }}
            </div>
            <div class="text-sm">{{ __('Was soll passieren?') }}</div>
            <div class="flex flex-wrap gap-2">
                <x-button
                    :text="__('Als eigenständiges Produkt aktivieren')"
                    color="primary"
                    wire:click="promoteToStandalone"
                    wire:flux-confirm.type.info="{{ __('Sicher? Das Produkt ist danach wieder verkaufbar.') }}"
                />
                <x-button
                    :text="__('Produkt deaktivieren')"
                    color="secondary"
                    flat
                    wire:click="deactivate"
                />
                <x-button
                    :text="__('Neue Variante anlegen')"
                    color="secondary"
                    flat
                    x-on:click="$tsui.open.modal('generate-variants-modal')"
                />
            </div>
        </div>
    </x-alert>
@endif
