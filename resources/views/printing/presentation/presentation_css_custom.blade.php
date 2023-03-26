<style>
    .presentation-main {
        height: 21cm;
        width: 29.7cm;
        min-width: 29.7cm;
        min-height: 21cm;
        display:flex;
        flex-direction: row;
        position: absolute;
    }

    .presentation-bar {
        font-family: 'Bebas Neue';
    }

    html {
        font-size: 62.5%;
        margin: 0;
    }
    body {
        margin: 0;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }
    @page  {
        size: A4 landscape;
        margin: 0;
    }

    @media print {
        html {
            margin: 0;
        }

        html {
            font-size: 62.5%;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        .presentation-image-header:empty, .presentation-bar:empty {
            display: none;
        }
    }
    @media screen {
        .pagedjs_page {
            margin: 0 auto;
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
