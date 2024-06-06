<div>
{{--    x-data="signature($wire,$refs)">--}}
{{--    <div class="flex flex-col items-center">--}}
{{--        <canvas x-ref="canvas"  width=500 height=200 class="bg-white rounded-md h-auto">--}}
{{--        </canvas>--}}
{{--        <div class="mt-4 mb-4 flex justify-center w-[100%] ">--}}
{{--            <button @click="clear" class="rounded-md px-2 py-1 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10 hover:bg-sky-500">Clear</button>--}}
{{--            <button @click="save" class="rounded-md px-2 py-1 text-center font-medium text-white shadow-sm ring-1 ring-slate-700/10 hover:bg-sky-500">Save</button>--}}
{{--        </div>--}}
{{--    </div>--}}
<div class="bg-white">
        {{$order->print()->renderView(FluxErp\View\Printing\Order\Invoice::class)}}
</div>


</div>

