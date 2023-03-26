@if($default)
    <div {{ $attributes }}>
        <svg class="relative m-auto h-full w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 198 181">
            <defs>
                <style>.f {
                        fill:  {{ $attributes->get('fill') ?? 'url(#d)' }};
                    }</style>
            </defs>
            <g id="b">
                <g id="c">
                    <g>
                        <path class="f"
                              d="M127.97,110.76L66.53,35.31h-17.03v111.43h20.53V71.28l61.44,75.45h17.03V61.73h-20.53v49.03Zm9.84-56.45c3.12,0,5.68-1,7.69-3.01,2-2,3.01-4.48,3.01-7.42,0-2.76-1.03-5.06-3.07-6.88-2.05-1.83-4.59-2.74-7.62-2.74s-5.57,.96-7.62,2.87c-2.05,1.92-3.07,4.3-3.07,7.15s1.02,5.24,3.07,7.15c2.05,1.92,4.59,2.87,7.62,2.87Z"/>
                    </g>
                </g>
            </g>
        </svg>
    </div>
@else
    <div class="h-full w-full bg-contain bg-center bg-no-repeat transition-all" style="background-image: url('{{ $logo?->getUrl() }}')">
    </div>
@endif
