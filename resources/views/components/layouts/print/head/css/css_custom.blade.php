<style>

    body {
        font-family: 'Montserrat', sans-serif;
        font-size: smaller;
        margin: 0 auto;
        width: 210mm;
        background-color: white;
    }

    .header-text {
        padding-bottom: 15px;
    }

    .header-images {
        display: inline-flex;
        align-items: center;
        height: 110px;
        width: 100%;
    }

    .header-client {
        margin-top: -5px;
    }

    .addresses {
        padding-top: 20mm;
        width: 100%;
        display: flex;
        min-height: 180px;
    }

    .invoice-address {
        width: 400px;
    }

    .delivery-address, .infoblock-right {
        padding-left: 3mm;
    }

    .rennergyLogo {
        height: auto;
        width: auto;
        max-width: 200px;
        max-height: 200px;
        transform: scale(0.9);
    }

    .PVHeiz {
        height: auto;
        width: auto;
        max-width: 70px;
        max-height: 70px;
        display: inline-block;
        position: relative;
        transform: scale(0.9);
    }

    .Ebox {
        height: auto;
        width: auto;
        max-width: 120px;
        max-height: 120px;
        display: inline-block;
        position: relative;
        margin-left: auto;
        transform: scale(0.9);
    }

    .subject {
        font-size: large;
        font-weight: bold;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .title {
        position: running(titleRunning);
    }

    @page {
        @top-center {
            content: element(titleRunning)
        }
    }

    footer {
        height: 27mm;
    }

    .footer {
        position: running(footerRunning);
    }

    .footer-content {
        padding-top: 4mm;
        text-align: left;
        font-size: 6pt;
    }

    @page {
        @bottom-center {
            content: element(footerRunning)
        }
    }

    .page-count {
        padding-top: 10px;
        padding-bottom: 5px;
    }

    .page-count:after {
        counter-increment: page;
        content: "Seite " counter(page) " von " counter(pages);

    }

    page-break {
        break-before: page;
    }

    p {
        break-after: page;
    }

    main, .footer-content, .following-pages-header {
        margin-left: 23mm;
        margin-right: 50px;
    }

    .header-images {
        padding-left: 15mm;
        padding-right: 50px;
    }

    @page {
        size: A4;
        margin-left: 0;
        margin-right: 0;
        margin-top: 48mm;
    }

    table {
        page-break-inside: auto;
        width: 100%;
        border-spacing: 0;
        border-collapse: collapse;
    }

    .pagedjs_first_page .following-pages-header {
        display: none;
    }

    .following-pages-header {
        padding-top: 10px;
    }

    table tbody tr:first-child {
        border: 0 !important;
    }

    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    .client {
        display: inline-block;
        border-bottom: 1px solid black;
        padding-bottom: 2px;
        font-size: 8pt;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    .theadrow, .totalsum {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .totalsumrow .row {
        padding-bottom: 15px;
    }

    tr > td {
        padding-bottom: 1em;
        padding-top: 3px;
    }

    header .theadrow {
        display: flex;
    }

    .theadrow th, .theadrow div, .totalsum {
        vertical-align: bottom;
        text-align: left;
        font-weight: normal;
        padding-left: 10px;
        padding-right: 10px;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    tr td {
        vertical-align: top;
    }

    .td-number {
        text-align: right;
    }

    .th-single {
        min-width: 80px;
    }

    .infoblock-bottom, .infoblock-td {
        display: flex;
    }

    .infoblock-left .infoblock-label {
        width: 100px;
    }

    .infoblock-bottom .pl-10 {
        width: 200px;
    }

    .infoblock-right .infoblock-label {
        width: 170px;
    }

    .header-text {
        padding-top: 3mm;
    }

    .pl-10 {
        padding-left: 10px;
    }

    .tc-1 {
        width: 54px;
        padding-left: 10px;
    }

    .tc-2 {
        width: 66px;
        text-align: center;
    }

    .tc-3 {
        width: 340px;
    }

    .tc-5 {
        padding-right: 10px;
    }

    .header-text hr {
        color: black;
        margin-top: 25px;

    }

    [data-break-before='page'] .following-pages-header {
        display: none;
    }

    @media screen {
        .pagedjs_page {
            margin-top:25px;
            box-shadow: rgba(50, 50, 93, 0.25) 0px 2px 5px -1px, rgba(0, 0, 0, 0.3) 0px 1px 3px -1px;
        }

        .pagedjs_sheet {
            background-color: #ffffff;
        }

        html, .pagedjs_pages {
            background-color: #eeeeee;
        }

        .pagedjs_pages:last-child {
            margin-bottom: 25px;
        }
    }
</style>
