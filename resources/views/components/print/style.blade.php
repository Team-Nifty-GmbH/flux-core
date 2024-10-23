<style>

    @section('style.font')
        html {
            font-family: 'Montserrat';
        }
    @show

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

    @section('style.header')
        header {
            top: -20mm;
        }
    @show

    @section('style.footer')
        footer {
            bottom: -30mm;
            padding-bottom: 10mm;
        }
    @show

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
        @foreach($pageCss as $key => $css)
            {{ $key }}: {{ is_array($css) ? implode(' ', $css) : $css }};
        @endforeach
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
