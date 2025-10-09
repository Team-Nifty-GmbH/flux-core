<style>
    @section('style.font')
        html {
            font-family: 'Montserrat';
        }
    @show


    li {
        list-style-type: disc;
    }

    ul {
        padding-left: 15px;
    }

    li > p {
        margin-bottom: 0;
    }

    .bg-even-children >:nth-child(even){
        background: #F2F4F7;
    }

    .bg-uneven:nth-child(odd){
        background: #F2F4F7;
    }

    .black-bar {
        background-color: black;
        width: 1.75rem;
        height: 0.25rem;
    }

    .logo{
        height:70px;
    }

    .logo-small{
        height:50px;
    }

    @page {
      {{ 'margin: ' . implode(' ', data_get($pageCss,'margin',[])) . ';' }}
    }

    @page :first {
        {{ 'margin: ' . implode(' ', data_get($pageCss,'margin_first_page',[])) . ';' }}
    }

    @page {
        .page-count:before {
            content: counter(page);
        }
    }
    {{-- without this pdf generation would crash --}}

    @media screen {
        @if(!$isPreview)
        {{-- preview related --}}
            body {
                width: 21cm;
                margin: 0 auto !important;
                padding: {{ implode(' ', data_get($pageCss,'margin_preview_view',[])) }};
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.5);
                border-radius: 10px;
            }

            footer {
                display: none;
            }

            header {
                display: none;
            }
        @else
            {{-- print related --}}
            body {
                margin: 0;
                padding: 0;
            }

            header {
                display: block;
                position: fixed;
                top: -{{ data_get($pageCss,'header_height','0') }};
                left: 0;
                right: 0;
            }

            footer {
                display: block;
                position: fixed;
                bottom: -{{ data_get($pageCss,'footer_height','0') }};
                left: 0;
                right: 0;
            }

            {{-- due to 0 margin for on first page --}}
            {{-- it is needed to add margin-top on first-page-header --}}
            .first-page-header-margin-top {
                margin-top: {{ data_get($pageCss,'first_page_header_margin_top','0') }};
            }

            .page-count:before {
                content: counter(page);
            }

        @endif
        html {
            background: #f5f5f5;
            overflow-y: auto;
        }

    }
</style>
