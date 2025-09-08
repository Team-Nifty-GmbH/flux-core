import { roundToOneDecimal, STEP } from '../../components/utils/print/utils.js';
export default function ($headerStore, $firstPageHeaderStore, $footerStore) {
    return {
        async onInit($wire, $refs) {
            this._loading = true;
            this.pxPerCm = $refs['scale'].offsetWidth;
            this.pyPerCm = $refs['scale'].offsetHeight;
            $headerStore.onInit(this.pxPerCm, this.pyPerCm);
            $firstPageHeaderStore.onInit(this.pxPerCm, this.pyPerCm);
            $footerStore.onInit(this.pxPerCm, this.pyPerCm);
            await this.register($wire);
            // to register other stores - main store needs to be registered first
            // to render correctly the margins first - if registrations run in parallel
            // the child elements will have incorrect width and height
            await Promise.all([
                $headerStore.register($wire, $refs),
                $firstPageHeaderStore.register($wire, $refs),
                $footerStore.register($wire, $refs),
            ]);
            this._loading = false;
        },
        editMargin: false,
        editHeader: false,
        editFirstPageHeader: false,
        editFooter: true,
        async selectClient(e, $wire, $refs) {
            this._loading = true;
            await $wire.selectClient(e.target.value);
            await this.reload();
            await $headerStore.reload($refs);
            await $firstPageHeaderStore.reload($refs);
            await $footerStore.reload($refs);
            this._loading = false;
        },
        pxPerCm: null,
        pyPerCm: null,
        _loading: false,
        _component: null,
        startPointVertical: null,
        startPointHorizontal: null,
        _marginTop: 2,
        _marginBottom: 2,
        _marginLeft: 2,
        _marginRight: 2,
        isTopClicked: false,
        isBottomClicked: false,
        isLeftClicked: false,
        isRightClicked: false,
        get loading() {
            return this._loading;
        },
        set loading(value) {
            this._loading = value;
        },
        get component() {
            if (this._component === null) {
                throw new Error('Component not initialized');
            }
            return this._component();
        },
        get marginLeft() {
            return `${this._marginLeft}cm`;
        },
        get marginRight() {
            return `${this._marginRight}cm`;
        },
        get marginTop() {
            return `${this._marginTop}cm`;
        },
        get marginBottom() {
            return `${this._marginBottom}cm`;
        },
        // TODO: rename to isAnyMarginSideClicked
        get isAnyClicked() {
            return (
                this.isTopClicked ||
                this.isBottomClicked ||
                this.isLeftClicked ||
                this.isRightClicked
            );
        },
        get anyEdit() {
            return (
                this.editMargin ||
                this.editFooter ||
                this.editHeader ||
                this.editFirstPageHeader
            );
        },
        get anyEdiorOpen() {
            return (
                $headerStore.snippetEditorXData !== null ||
                $firstPageHeaderStore.snippetEditorXData !== null ||
                $footerStore.snippetEditorXData !== null
            );
        },
        // TODO: rename to onMouseDownMargin
        onMouseDown(e, side) {
            switch (side) {
                case 'margin-top':
                    this.isTopClicked = true;
                    this.startPointVertical = e.clientY;
                    break;
                case 'margin-bottom':
                    this.isBottomClicked = true;
                    this.startPointVertical = e.clientY;
                    break;
                case 'margin-left':
                    this.isLeftClicked = true;
                    this.startPointHorizontal = e.clientX;
                    break;
                case 'margin-right':
                    this.isRightClicked = true;
                    this.startPointHorizontal = e.clientX;
                    break;
                default:
                    console.warn('Unknown side clicked:', side);
            }
        },
        // TODO: rename to onMouseUpMargin
        onMouseUp() {
            if (this.isTopClicked) {
                this.isTopClicked = false;
                this.startPointVertical = null;
            }

            if (this.isBottomClicked) {
                this.isBottomClicked = false;
                this.startPointVertical = null;
            }

            if (this.isLeftClicked) {
                this.isLeftClicked = false;
                this.startPointHorizontal = null;
            }

            if (this.isRightClicked) {
                this.isRightClicked = false;
                this.startPointHorizontal = null;
            }

            // adjust the position of the header/footer/first-page-header elements if there is overlap with the margin
            $footerStore.repositionOnMouseUp();
            $headerStore.repositionOnMouseUp();
            $firstPageHeaderStore.repositionOnMouseUp();
        },
        // TODO: rename to onMouseMoveMargin
        onMouseMove(e) {
            // handler for resizing margins
            if (this.isTopClicked && this.startPointVertical !== null) {
                const delta =
                    (this.startPointVertical - e.clientY) / this.pyPerCm;
                if (Math.abs(delta) >= 0.1) {
                    const value = roundToOneDecimal(
                        this._marginTop + 0.1 * (delta > 0 ? -1 : 1),
                    );
                    this._marginTop = value < 5 ? Math.max(0, value) : 5;
                    this.startPointVertical = e.clientY;
                }
            }

            if (this.isBottomClicked && this.startPointVertical !== null) {
                const delta =
                    (this.startPointVertical - e.clientY) / this.pyPerCm;
                if (Math.abs(delta) >= STEP) {
                    const value = roundToOneDecimal(
                        this._marginBottom + STEP * (delta > 0 ? 1 : -1),
                    );
                    this._marginBottom = value < 5 ? Math.max(0, value) : 5;
                    this.startPointVertical = e.clientY;
                }
            }

            if (this.isLeftClicked && this.startPointHorizontal !== null) {
                const delta =
                    (this.startPointHorizontal - e.clientX) / this.pxPerCm;
                if (Math.abs(delta) >= STEP) {
                    const value = roundToOneDecimal(
                        this._marginLeft + STEP * (delta > 0 ? -1 : 1),
                    );
                    this._marginLeft = value < 5 ? Math.max(0, value) : 5;
                    this.startPointHorizontal = e.clientX;
                }
            }

            if (this.isRightClicked && this.startPointHorizontal !== null) {
                const delta =
                    (this.startPointHorizontal - e.clientX) / this.pxPerCm;
                if (Math.abs(delta) >= STEP) {
                    const value = roundToOneDecimal(
                        this._marginRight + STEP * (delta > 0 ? 1 : -1),
                    );
                    this._marginRight = value < 5 ? Math.max(0, value) : 5;
                    this.startPointHorizontal = e.clientX;
                }
            }
        },
        toggleEditMargin() {
            this.editMargin = !this.editMargin;
        },
        toggleEditFooter() {
            this.editFooter = !this.editFooter;
        },
        toggleEditHeader() {
            this.editHeader = !this.editHeader;
        },
        toggleEditFirstPageHeader() {
            this.editFirstPageHeader = !this.editFirstPageHeader;
        },
        async closeEditor($refs) {
            this._loading = true;
            await this.reload();
            await $footerStore.reload($refs, false);
            await $headerStore.reload($refs, false);
            await $firstPageHeaderStore.reload($refs, false);
            this.editMargin = false;
            this.editFooter = false;
            this.editHeader = false;
            this.editFirstPageHeader = false;
            this._loading = false;
        },
        _setMargin(margin) {
            if (!Array.isArray(margin) && Object.keys(margin).length > 0) {
                this._marginTop = margin.marginTop;
                this._marginBottom = margin.marginBottom;
                this._marginLeft = margin.marginLeft;
                this._marginRight = margin.marginRight;
            } else {
                this._marginTop = 2;
                this._marginLeft = 2;
                this._marginBottom = 2;
                this._marginRight = 2;
            }
        },
        async register($wire) {
            this._component = () => $wire;
            const margin = await $wire.get('form.margin');
            this._setMargin(margin);
        },
        async reload() {
            const margin = await this.component.get('form.margin');
            this._setMargin(margin);
        },
        prepareToSubmit() {
            return {
                marginTop: this._marginTop,
                marginBottom: this._marginBottom,
                marginLeft: this._marginLeft,
                marginRight: this._marginRight,
            };
        },
        async submit($wire, $refs) {
            this._loading = true;
            const margins = this.prepareToSubmit();
            const header = await $headerStore.prepareToSubmit();
            const firstPageHeader =
                await $firstPageHeaderStore.prepareToSubmit();
            const footer = await $footerStore.prepareToSubmit();
            await Promise.all([
                $wire.set('form.footer', footer, false),
                $wire.set('form.header', header, false),
                $wire.set('form.margin', margins, false),
                $wire.set('form.first_page_header', firstPageHeader, false),
            ]);

            const response = await $wire.save();
            if (response) {
                this.editMargin = false;
                this.editFooter = false;
                this.editHeader = false;
                this.editFirstPageHeader = false;
                // due to nature of a file upload - it is not renderlless - Livewire will drive the re-render
                // all the elements will disappear - hence stores need to be reloaded
                if (
                    $footerStore.temporaryVisibleMedia.length > 0 ||
                    $headerStore.temporaryVisibleMedia.length > 0 ||
                    $firstPageHeaderStore.temporaryVisibleMedia.length > 0
                ) {
                    // reload
                    await this.reload();
                    await $footerStore.reload($refs);
                    await $headerStore.reload($refs);
                    await $firstPageHeaderStore.reload($refs);
                }
            }
            this._loading = false;
        },
    };
}
