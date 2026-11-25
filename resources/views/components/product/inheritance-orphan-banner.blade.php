@if ($visible)
    <x-alert color="amber">
        <div class="space-y-3">
            <div class="font-semibold">
                {{ __('This product had variants, none of them is active anymore.') }}
            </div>
            <div class="text-sm">{{ __('What should happen?') }}</div>
            <div class="flex flex-wrap gap-2">
                <x-button
                    :text="__('Activate as a standalone product')"
                    color="primary"
                    wire:click="promoteToStandalone()"
                    wire:flux-confirm.type.info="{{ __('The product is sellable again afterwards. Continue?') }}"
                />
                <x-button
                    :text="__('Deactivate product')"
                    color="secondary"
                    flat
                    wire:click="deactivate()"
                />
                <x-button
                    :text="__('Create a new variant')"
                    color="secondary"
                    flat
                    x-on:click="$tsui.open.modal('generate-variants-modal')"
                />
            </div>
        </div>
    </x-alert>
@endif
