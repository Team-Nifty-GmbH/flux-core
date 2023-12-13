<div>
    <x-modal name="edit-vat-rate">
        <x-card>
            <div class="flex flex-col gap-4">
                <x-input wire:model="vatRate.name" :label="__('Name')" />
                <x-inputs.number wire:model="vatRate.rate_percentage_frontend" :label="__('Rate Percentage')" />
                <x-editor wire:model="vatRate.footer_text" :label="__('Footer Text')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(\FluxErp\Actions\VatRate\DeleteVatRate::canPerformAction(false))
                        <div x-bind:class="$wire.vatRate.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                x-on:click="close"
                                wire:click="delete().then((success) => { if(success) close()})"
                                wire:confirm.icon.error="{{ trans('wire:confirm.delete', ['model' => __('Vat Rate')]) }}"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
