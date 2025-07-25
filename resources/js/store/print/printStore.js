export default function ($footerStore) {
    return {
        async onInit($wire, $refs) {
            this.pxPerCm = $refs['scale'].offsetWidth;
            this.pyPerCm = $refs['scale'].offsetHeight;
            $footerStore.onInit(this.pxPerCm, this.pyPerCm);
            await this.register($wire);
        },
        editMargin: false,
        editFooter: false,
        editHeader: false,
        async selectClient(e, $wire, $refs) {
            await $wire.selectClient(e.target.value);
            await $footerStore.reload($refs);
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
            return $footerStore.loading || this._loading;
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
            return this.editMargin || this.editFooter || this.editHeader;
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
        },
        // TODO: rename to onMouseMoveMargin
        onMouseMove(e) {
            // handler for resizing margins
            if (this.isTopClicked && this.startPointVertical !== null) {
                const delta =
                    (this.startPointVertical - e.clientY) / this.pyPerCm;
                if (Math.abs(delta) >= 0.1) {
                    this._marginTop = Math.max(
                        0,
                        Math.round(
                            (this._marginTop + 0.1 * (delta > 0 ? -1 : 1)) * 10,
                        ) / 10,
                    );
                    this.startPointVertical = e.clientY;
                }
            }

            if (this.isBottomClicked && this.startPointVertical !== null) {
                const delta =
                    (this.startPointVertical - e.clientY) / this.pyPerCm;
                if (Math.abs(delta) >= 0.1) {
                    this._marginBottom = Math.max(
                        0,
                        Math.round(
                            (this._marginBottom + 0.1 * (delta > 0 ? 1 : -1)) *
                                10,
                        ) / 10,
                    );
                    this.startPointVertical = e.clientY;
                }
            }

            if (this.isLeftClicked && this.startPointHorizontal !== null) {
                const delta =
                    (this.startPointHorizontal - e.clientX) / this.pxPerCm;
                if (Math.abs(delta) >= 0.1) {
                    this._marginLeft = Math.max(
                        0,
                        Math.round(
                            (this._marginLeft + 0.1 * (delta > 0 ? -1 : 1)) *
                                10,
                        ) / 10,
                    );
                    this.startPointHorizontal = e.clientX;
                }
            }

            if (this.isRightClicked && this.startPointHorizontal !== null) {
                const delta =
                    (this.startPointHorizontal - e.clientX) / this.pxPerCm;
                if (Math.abs(delta) >= 0.1) {
                    this._marginRight = Math.max(
                        0,
                        Math.round(
                            (this._marginRight + 0.1 * (delta > 0 ? 1 : -1)) *
                                10,
                        ) / 10,
                    );
                    this.startPointHorizontal = e.clientX;
                }
            }
        },
        toggleEditMargin() {
            console.log(this);
            this.editMargin = !this.editMargin;
        },
        toggleEditFooter() {
            this.editFooter = !this.editFooter;
        },
        toggleEditHeader() {
            this.editHeader = !this.editHeader;
        },
        async closeEditor($refs) {
            await $footerStore.reload($refs, false);
            this.editMargin = false;
            this.editFooter = false;
            this.editHeader = false;
        },
        async register($wire) {
            this._loading = true;
            this._component = () => $wire;

            const margin = await $wire.get('form.margin');
            if (!Array.isArray(margin) && Object.keys(margin).length > 0) {
                this._marginBottom = margin.marginBottom || 2;
                this._marginTop = margin.marginTop || 2;
                this._marginLeft = margin.marginLeft || 2;
                this._marginRight = margin.marginRight || 2;
            }
            this._loading = false;
        },
        async reload($refs) {},
        prepareToSubmit() {
            return {
                marginTop: this._marginTop,
                marginBottom: this._marginBottom,
                marginLeft: this._marginLeft,
                marginRight: this._marginRight,
            };
        },
        async submit($wire) {
            const margins = this.prepareToSubmit();
            const footer = $footerStore.prepareToSubmit();
            await $wire.set('form.footer', footer, false);
            await $wire.set('form.margin', margins, false);
            const response = await $wire.save();
            if (response) {
                this.editMargin = false;
                this.editFooter = false;
                this.editHeader = false;
            }
        },
    };
}
