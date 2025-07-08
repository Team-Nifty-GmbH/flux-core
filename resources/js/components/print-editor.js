window.printEditorMain = function () {
    return {
        onInit() {
            this.pxPerCm = this.$refs['scale'].offsetWidth;
            this.pyPerCm = this.$refs['scale'].offsetHeight;
        },
        editMargin: false,
        editFooter: false,
        editHeader: false,
        toggleEditMargin() {
            this.editMargin = !this.editMargin;
        },
        toggleEditFooter() {
            this.editFooter = !this.editFooter;
        },
        toggleEditHeader() {
            this.editHeader = !this.editHeader;
        },
        pxPerCm: null,
        pyPerCm: null,
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
                            (this._marginTop + 0.1 * (delta > 0 ? -1 : 1)) *
                                100,
                        ) / 100,
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
                                100,
                        ) / 100,
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
                                100,
                        ) / 100,
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
                                100,
                        ) / 100,
                    );
                    this.startPointHorizontal = e.clientX;
                }
            }
        },
        closeEditor() {
            // TODO: reset to previous state - reload from server?
            this.editMargin = false;
            this.editFooter = false;
            this.editHeader = false;
        },
        async submit(column, data) {},
    };
};

window.printEditorHeader = function (parent) {
    return {};
};

window.printEditorFooter = function (parent) {
    return {
        _footerHeight: null,
        _minFooterHeight: null,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        startPointFooterVertical: null,
        onInitFooter() {
            // round ceil to 0.1 cm
            this._footerHeight =
                Math.ceil(
                    (10 * this.$refs['footer'].offsetHeight) / parent.pyPerCm,
                ) / 10;

            this._minFooterHeight = 1.7;
        },
        onMouseDownFooter(e) {
            this.isFooterClicked = true;
            this.startPointFooterVertical = e.clientY;
        },
        onMouseUpFooter() {
            if (this.isFooterClicked) {
                this.isFooterClicked = false;
                this.startPointFooterVertical = null;
            }
        },
        onMouseMoveFooter(e) {
            if (
                this.isFooterClicked &&
                this.startPointFooterVertical !== null
            ) {
                const delta =
                    (this.startPointFooterVertical - e.clientY) /
                    parent.pyPerCm;
                if (Math.abs(delta) >= 0.1) {
                    const newHeight = Math.max(
                        0,
                        Math.round(
                            (this._footerHeight + 0.1 * (delta > 0 ? 1 : -1)) *
                                100,
                        ) / 100,
                    );
                    if (
                        newHeight >= this._minFooterHeight &&
                        newHeight <= this._maxFooterHeight
                    ) {
                        this._footerHeight = newHeight;
                    } else {
                        return;
                    }
                    this.startPointFooterVertical = e.clientY;
                }
            }
        },
        get footerHeight() {
            return `${this._footerHeight}cm`;
        },
        get logoFooterSize() {
            if (this._minFooterHeight !== null) {
                return `${this._minFooterHeight}cm`;
            } else {
                return 'auto';
            }
        },
    };
};
