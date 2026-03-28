<style>
    @section('style.font')
        html {
            font-family: 'Montserrat';
        }
    @show

    p {
        margin-bottom: 2px;
    }

    p:empty {
        min-height: 1em;
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

    /* Tailwind utility classes for print templates */

    /* Display & Position */
    .fixed { position: fixed; }
    .absolute { position: absolute; }
    .left-0 { left: 0; }
    .right-0 { right: 0; }
    .z-10 { z-index: 10; }
    .flex { display: flex; }
    .grid { display: grid; }
    .inline-block { display: inline-block; }
    .float-left { float: left; }
    .float-right { float: right; }
    .clear-both { clear: both; }
    .overflow-auto { overflow: auto; }
    .break-inside-avoid { break-inside: avoid; }
    .justify-end { justify-content: flex-end; }
    .content-center { align-content: center; }

    /* Width & Height */
    .w-full { width: 100%; }
    .w-1\/2 { width: 50%; }
    .w-44 { width: 11rem; }
    .w-0 { width: 0; }
    .h-full { height: 100%; }
    .h-32 { height: 8rem; }
    .h-4 { height: 1rem; }
    .h-auto { height: auto; }
    .max-w-full { max-width: 100%; }
    .max-w-sm { max-width: 24rem; }
    .max-h-72 { max-height: 18rem; }
    .max-h-32 { max-height: 8rem; }

    /* Spacing */
    .m-auto { margin: auto; }
    .mt-8 { margin-top: 2rem; }
    .mt-10 { margin-top: 2.5rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .p-0 { padding: 0; }
    .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
    .py-0 { padding-top: 0; padding-bottom: 0; }
    .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
    .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
    .pt-4 { padding-top: 1rem; }
    .pt-6 { padding-top: 1.5rem; }
    .pt-8 { padding-top: 2rem; }
    .pt-10 { padding-top: 2.5rem; }
    .pt-20 { padding-top: 5rem; }
    .pb-1 { padding-bottom: 0.25rem; }
    .pb-4 { padding-bottom: 1rem; }
    .pb-6 { padding-bottom: 1.5rem; }
    .pb-16 { padding-bottom: 4rem; }
    .pl-3 { padding-left: 0.75rem; }
    .pl-6 { padding-left: 1.5rem; }
    .pl-12 { padding-left: 3rem; }
    .pr-8 { padding-right: 2rem; }

    /* Typography */
    .text-3xs { font-size: 0.5rem; }
    .text-2xs { font-size: 0.625rem; }
    .text-xs { font-size: 0.75rem; line-height: 1rem; }
    .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
    .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
    .text-5xl { font-size: 3rem; line-height: 1; }
    .font-light { font-weight: 300; }
    .font-normal { font-weight: 400; }
    .font-semibold { font-weight: 600; }
    .font-bold { font-weight: 700; }
    .font-italic { font-style: italic; }
    .not-italic { font-style: normal; }
    .uppercase { text-transform: uppercase; }
    .text-left { text-align: left; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .leading-none { line-height: 1; }
    .leading-3 { line-height: 0.75rem; }
    .whitespace-nowrap { white-space: nowrap; }

    /* Borders */
    .border-b { border-bottom: 1px solid; }
    .border-t { border-top: 1px solid; }
    .border-b-2 { border-bottom: 2px solid; }
    .border-separate { border-collapse: separate; }
    .border-spacing-x-2 { border-spacing: 0.5rem 0; }

    /* Table */
    .table-auto { table-layout: auto; }
    .align-top { vertical-align: top; }
    .align-text-top { vertical-align: text-top; }

    /* Background */
    .bg-white { background-color: white; }

    /* Prose (minimal for print) */
    .prose-xs { font-size: 0.75rem; line-height: 1.4; }
    .prose-sm { font-size: 0.875rem; line-height: 1.5; }

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

    @media print {
        body {
            margin-bottom: 30px;
        }
    }
</style>
