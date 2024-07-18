<div>
    @if(!is_null($className))
        <div>
            {{ $order->print()->renderView($className) }}
            <div x-data="signature($wire,$refs)" class="bg-gray-100 mt-12 lg:pt-10 lg:pr-10 ">
                <div class="flex flex-col items-center lg:items-end">
                    <div class="flex flex-col lg:flex-row w-full items-center justify-between">
                        <div class="flex-1 justify-center">
                            <div x-cloak class="pt-10 lg:pt-0 flex flex-col items-center justify-center" x-show="error || id">
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
                        <div class="flex flex-col items-end pt-10 lg:pt-0">
                            <h1 class="text-xl mb-2">{{__('Signe Here')}}</h1>
                        <canvas x-ref="canvas" height="200" width="500" class="rounded-md h-auto">
                        </canvas>
                        </div>
                    </div>
                    <div class="mt-4 h-8 mb-4 lg:mr-0 mr-10 flex justify-end w-full space-x-5">
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
