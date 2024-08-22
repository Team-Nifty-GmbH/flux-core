<html class="h-full text-sm">
@use('FluxErp\Facades\Asset')
@use('FluxErp\Providers\ViewServiceProvider')
<head>
    @section('head')
        @section('head.meta')
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="currency-code" content="{{ \FluxErp\Models\Currency::default()?->iso }}">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>{{ $title ?? $subject ?? '' }}</title>
        @show
        @section('head.assets')
            @vite(ViewServiceProvider::getRealPackageAssetPath('/resources/css/app.css', 'team-nifty-gmbh/flux-erp'))
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        @show
        @section('head.styles')
            <style>
                html {
                    font-family: 'Montserrat';
                }

                p {
                    margin-bottom: 10px;
                }

                li {
                    list-style-type: disc;
                }

                ul {
                    padding-left: 15px;
                }

                li > p {
                    margin-bottom: 0;
                }

                tr>td:first-child, tr>th:first-child {
                    padding-left: 10px;
                }

                tr>td:last-child, tr>th:last-child {
                    padding-right: 10px;
                }

                .cover-page{
                    margin-top: -90px;
                    background-color: white;
                }

                .border-black {
                    border-color: black;
                }

                .line-through {
                    text-decoration: line-through;
                }

                .black-bar {
                    background-color: black;
                    width: 1.75rem;
                    height: 0.25rem;
                }

                header {
                    top: -20mm;
                }

                footer {
                    bottom: -30mm;
                    padding-bottom: 10mm;
                }

                .logo{
                    height:70px;
                }

                .logo-small{
                    height:50px;
                }

                .footer-logo {
                    transform: translateY(-50%);
                    background-color: white;
                    padding-left: 3mm;
                    padding-right: 3mm;
                }

                .bg-even-children >:nth-child(even){
                    background: #F2F4F7;
                }

                .bg-uneven:nth-child(odd){
                    background: #F2F4F7;
                }

                .border-semi-black {
                    border-color: #667085;
                }

                @page {
                    size: A4;
                    margin: 32mm 20mm 28mm 18mm;
                    bleed: 6mm;
                }

                .page-count:after {
                    content: "{{ __('Page') }} " counter(page) " {{ __('of') }} DOMPDF_PAGE_COUNT_PLACEHOLDER";
                }

                @media screen {
                    .cover-page{
                        margin-top: 0;
                    }

                    footer {
                        display: none;
                    }

                    body {
                        max-width: 80rem;
                        margin: 0 auto !important;
                        padding: 20mm;
                        background: white;
                        box-shadow: 0 0 10px rgba(0,0,0,0.5);
                        border-radius: 10px;
                    }

                    html {
                        background: #f5f5f5;
                    }
                }
            </style>
        @show
    @show
</head>
<body class="text-xs">
    <x-flux::print.header />
    <x-flux::print.footer />
    {!! $slot !!}
    @if($signaturePath)
        <div class="flex justify-end mt-10">
            <div>
                <img
                    src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath)) }}"
                    alt="{{ __('Signature') }}"
                />
            </div>
        </div>
    @endif
</body>
</html>
