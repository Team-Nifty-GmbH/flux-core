{{--TODO: rename to a4-page --}}
<div
    x-data="printEditor()"
    class="min-w-[21cm] w-[21cm] min-h-[29.7cm] h-[29.7cm] bg-white shadow">
    <div class="w-full h-full bg-primary-300" :style="{'padding-left': marginLeft, 'padding-right': marginRight, 'padding-top': marginTop, 'padding-bottom': marginBottom}">
        {{-- content --}}
        <div class="relative w-full h-full bg-white border-gray-500 border-2">
            <div class="rounded-full bg-purple-300 absolute w-6 h-6 top-0 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="rounded-full bg-green-300 absolute w-6 h-6 left-0 top-1/2 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="rounded-full bg-yellow-300 absolute w-6 h-6 bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2"></div>
            <div class="rounded-full bg-teal-300 absolute w-6 h-6 right-0 top-1/2 translate-x-1/2 -translate-y-1/2"></div>
        </div>
    </div>
</div>
