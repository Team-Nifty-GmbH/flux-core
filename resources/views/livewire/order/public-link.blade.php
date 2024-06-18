<div>
    {{ $order->print()->renderView(FluxErp\View\Printing\Order\Invoice::class) }}
    <div x-data="signature($wire,$refs)" class="bg-gray-100 pt-10 pr-10">
        <div class="flex flex-col items-end">
            <div class="flex w-full justify-between">
                <div class="flex-1 flex items-center justify-center">
                    <div x-cloak class="flex flex-col items-center justify-center" x-show="error || id">
                        <div class="w-10 h-10">
                            <template x-if="error">
                                <x-icon name="exclamation"/>
                            </template>
                            <template x-if="id && !error">
                                <x-icon name="check"/>
                            </template>
                        </div>
                        <template x-if="error">
                            <p class="text-2xl">{{__("Upload Failed")}}</p>
                        </template>
                        <template x-if="id && !error">
                            <p class="text-2xl">{{__("Signature saved")}}</p>
                        </template>
                    </div>
                </div>
                <canvas x-ref="canvas" width=500 height=200 class="rounded-md h-auto">
                </canvas>
            </div>
            <div class="mt-4 h-8 mb-4 flex justify-end w-full space-x-5">
                <x-button
                    x-cloak
                    x-show="!isEmpty"
                    x-on:click="clear"
                    class="bg-red-500 rounded-md px-4 py-2 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10 hover:bg-red-600"
                    :label="__('Clear')"
                />
                <x-button
                    x-cloak
                    x-show="!isEmpty"
                    x-on:click="save"
                    class="bg-primary-600 rounded-md px-4 py-2 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10"
                    :label="__('Save')"
                />
            </div>
        </div>
    </div>
</div>
