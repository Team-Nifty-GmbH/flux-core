<div>
    @if(!is_null($className))
        <div>
            {{ $order->print()->renderView($className) }}
            <div x-data="signature($wire,$refs)" class="bg-gray-100 mt-12 pt-10 pr-10">
                <div class="flex flex-col items-end">
                    <h1 class="text-xl mb-2">{{__('Signe Here')}}</h1>
                    <div class="flex w-full justify-between">
                        <div class="flex-1 flex items-center justify-center">
                            <div x-cloak class="flex flex-col items-center justify-center" x-show="error || id">
                                <div class="w-10 h-10">
                                    <template x-if="error">
                                        <x-icon name="exclamation" />
                                    </template>
                                    <template x-if="id && !error">
                                        <x-icon name="check" />
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
                            negative
                            :label="__('Clear')"
                        />
                        <x-button
                            x-cloak
                            x-show="!isEmpty"
                            x-on:click="save"
                            primary
                            :label="__('Save')"
                        />
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
