<header
    style="
        position: fixed;
        height: auto;
        width: 100%;
        background: white;
        text-align: center;
        font-weight: 300;
    "
>
    <div class="header-content">
        <div>
            @section('subject')
                <div
                    style="float: left; display: inline-block; text-align: left"
                >
                    <h2
                        style="
                            font-size: 20px;
                            line-height: 28px;
                            font-weight: 600;
                        "
                    >
                        {{ $subject ?? '' }}
                    </h2>
                    <div class="page-count" style="font-size: 12px"></div>
                </div>
            @show
            @section('logo')
                <div
                    style="
                        float: right;
                        display: inline-block;
                        max-height: 288px;
                        width: 176px;
                        text-align: right;
                    "
                >
                    <img
                        class="logo-small"
                        src="{{ $tenant->logo_small }}"
                        alt="logo-small"
                    />
                </div>
            @show
            <div style="clear: both"></div>
        </div>
    </div>
</header>
