<div>
    {{$order->print()->renderView(FluxErp\View\Printing\Order\Invoice::class)}}
    <div x-data="signature($wire,$refs)" class="bg-gray-100 pt-10 pr-10">
        <div class="flex flex-col items-end">
        <canvas x-ref="canvas" width=500 height=200 class=" rounded-md h-auto">
        </canvas>
        <div class="mt-4 h-8 mb-4 flex justify-end w-[100%] space-x-5">
            <button
                    x-cloak x-show="!isEmpty"
                    @click="clear"
                    class="bg-red-500 rounded-md px-4 py-2 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10 hover:bg-red-600">Clear</button>
            <button
                    x-cloak x-show="!isEmpty"
                    @click="save"
                    style="background-color: rgb(79 70 229)" class="bg-indigo rounded-md px-4 py-2 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10">Save</button>
            </div>
        </div>
        </div>
</div>


