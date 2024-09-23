@if($default)
    <div {{ $attributes }}>

        <svg class="relative m-auto h-full w-full" viewBox="0 0 351 377" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <style>.f {
                        fill:  {{ $attributes->get('fill') ?? 'url(#d)' }};
                    }</style>
            </defs>
            <path class="f" d="M204.853 276.557C195.935 267.64 191.477 257.98 191.477 244.604V132.396C191.477 119.02 195.935 109.36 204.853 100.443L304.428 0.867188L350.5 47.6824L209.311 188.128L350.5 329.317L304.428 376.133L204.853 276.557ZM0.5 329.317L141.689 188.128L0.5 47.6824L46.5722 0.867188L146.148 100.443C155.065 109.36 159.523 119.02 159.523 132.396V244.604C159.523 257.98 155.065 267.64 146.148 276.557L46.5722 376.133L0.5 329.317Z" fill="white"/>
        </svg>
    </div>
@else
    <div class="h-full w-full bg-contain bg-center bg-no-repeat transition-all" style="background-image: url('{{ $logo?->getUrl() }}')">
    </div>
@endif
