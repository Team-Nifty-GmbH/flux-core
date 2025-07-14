import {
    roundToTwoDecimal,
    roundToOneDecimal,
    moveHorizontal,
    moveVertical,
    moveDiagonal,
} from './utils/print/utils.js';

const STEP = 0.1; // step in cm

// TODO: add nextFrame to avoid blocking UI - changing margins,height is slow
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
    // TODO: add wtahcer for footer height changes - to adjust elements position if needed
    return {
        _footerHeight: null,
        _minFooterHeight: null,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        isClientClicked: false,
        isBankClicked: false,
        isLogoFooterClicked: false,
        _clientPosition: {
            top: 0,
            left: 0,
        },
        _logoFooterPosition: {
            top: 0,
            left: 0,
        },
        _bankPosition: {
            top: 0,
            right: 0,
        },
        startPointFooterVertical: null,
        _startPointClient: null,
        _startPointLogoFooter: null,
        _startPointBank: null,
        elementAlignment: null, // 'top', 'center', 'bottom'
        onInitFooter() {
            this._footerHeight = 1.7;
            this._minFooterHeight = 1.7;

            // on footer height change - element position should be adjusted
            // if an element overflows the footer height
            this.$watch('_footerHeight', (newHeight) => {
                // regarding client position
                if (
                    this._clientPosition.top +
                        this._elementSize('client').height >
                    newHeight
                ) {
                    this._clientPosition.top = Math.max(
                        0,
                        roundToOneDecimal(
                            newHeight - this._elementSize('client').height,
                        ),
                    );
                }
            });
        },
        onMouseDownFooter(e, element) {
            if (element === 'footer') {
                this.isFooterClicked = true;
                this.startPointFooterVertical = e.clientY;
            }

            if (element === 'client') {
                this.isClientClicked = true;
                this._startPointClient = { x: e.clientX, y: e.clientY };
            }

            if (element === 'logoFooter') {
                this.isLogoFooterClicked = true;
                this._startPointLogoFooter = { x: e.clientX, y: e.clientY };
            }
        },
        onMouseUpFooter() {
            if (this.isFooterClicked) {
                this.isFooterClicked = false;
                this.startPointFooterVertical = null;
            }
            if (this.isClientClicked) {
                this.isClientClicked = false;
                this._startPointClient = null;
            }
            if (this.isLogoFooterClicked) {
                this.isLogoFooterClicked = false;
                this._startPointLogoFooter = null;
            }

            if (this.isBankClicked) {
                this.isBankClicked = false;
                this._startPointBank = null;
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
                                10,
                        ) / 10,
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
        onMouseMoveFooterClient(e) {
            if (this.isClientClicked && this._startPointClient !== null) {
                const deltaX =
                    (e.clientX - this._startPointClient.x) / parent.pxPerCm;
                const deltaY =
                    (e.clientY - this._startPointClient.y) / parent.pyPerCm;
                if (Math.abs(deltaX) >= 0.1 && Math.abs(deltaY) < 0.1) {
                    const newValue = Math.max(
                        0,
                        roundToOneDecimal(
                            this._clientPosition.left +
                                0.1 * (deltaX > 0 ? 1 : -1),
                        ),
                    );

                    if (
                        newValue + this._elementSize('client').width <
                        this._elementSize('footer').width
                    ) {
                        this._clientPosition.left = newValue;
                    } else {
                        this._clientPosition.left = roundToOneDecimal(
                            this._elementSize('footer').width -
                                this._elementSize('client').width,
                        );
                    }
                    this._startPointClient.x = e.clientX;
                }

                if (Math.abs(deltaY) >= 0.1 && Math.abs(deltaX) < 0.1) {
                    const newValue = Math.max(
                        0,
                        roundToOneDecimal(
                            this._clientPosition.top +
                                0.1 * (deltaY > 0 ? 1 : -1),
                        ),
                    );

                    // need to add STEP to avoid footer overlap - due to rounding issues
                    // doesn't happen on x-axis
                    // TODO:
                    if (
                        newValue + this._elementSize('client').height + STEP <
                        this._footerHeight
                    ) {
                        this._clientPosition.top = newValue;
                    } else {
                        // round to 0.1 cm to avoid values like 1.24 - when on the bottom is - since the step is 0.1 cm
                        this._clientPosition.top = Math.max(
                            0,
                            roundToOneDecimal(
                                this._footerHeight -
                                    this._elementSize('client').height -
                                    STEP,
                            ),
                        );
                    }
                    this._startPointClient.y = e.clientY;
                }

                if (Math.abs(deltaX) >= 0.1 && Math.abs(deltaY) >= 0.1) {
                    const newValueX = Math.max(
                        0,
                        roundToOneDecimal(
                            this._clientPosition.left +
                                0.1 * (deltaX > 0 ? 1 : -1),
                        ),
                    );

                    const newValueY = Math.max(
                        0,
                        roundToOneDecimal(
                            this._clientPosition.top +
                                0.1 * (deltaY > 0 ? 1 : -1),
                        ),
                    );

                    if (
                        newValueX + this._elementSize('client').width <
                        this._elementSize('footer').width
                    ) {
                        this._clientPosition.left = newValueX;
                    } else {
                        this._clientPosition.left = roundToOneDecimal(
                            this._elementSize('footer').width -
                                this._elementSize('client').width,
                        );
                    }

                    if (
                        newValueY + this._elementSize('client').height + STEP <
                        this._footerHeight
                    ) {
                        this._clientPosition.top = newValueY;
                    } else {
                        this._clientPosition.top = roundToOneDecimal(
                            this._footerHeight -
                                this._elementSize('client').height -
                                STEP,
                        );
                    }

                    this._startPointClient.x = e.clientX;
                    this._startPointClient.y = e.clientY;
                }
            }
        },
        onMouseMoveFooterLogo(e) {
            if (
                this.isLogoFooterClicked &&
                this._startPointLogoFooter !== null
            ) {
                const deltaX =
                    (e.clientX - this._startPointLogoFooter.x) / parent.pxPerCm;
                const deltaY =
                    (e.clientY - this._startPointLogoFooter.y) / parent.pyPerCm;
                if (Math.abs(deltaX) >= 0.1 && Math.abs(deltaY) < 0.1) {
                    const newValue = roundToOneDecimal(
                        this._logoFooterPosition.left +
                            0.1 * (deltaX > 0 ? 1 : -1),
                    );

                    if (
                        newValue + this._elementSize('logoFooter').width <
                        this._elementSize('footer').width
                    ) {
                        this._logoFooterPosition.left = newValue;
                    } else {
                        this._logoFooterPosition.left = roundToOneDecimal(
                            this._elementSize('footer').width -
                                this._elementSize('logoFooter').width,
                        );
                    }
                    this._startPointLogoFooter.x = e.clientX;
                }
            }
        },
        get footerHeight() {
            return `${this._footerHeight}cm`;
        },
        get clientPositionTop() {
            if (this._clientPosition.top < 0) {
                return '0cm';
            }
            return `${this._clientPosition.top}cm`;
        },
        get clientPositionLeft() {
            if (this._clientPosition.left < 0) {
                return '0cm';
            }
            return `${this._clientPosition.left}cm`;
        },
        get absolutePositionImageTop() {
            if (this._logoFooterPosition.top < 0) {
                return '0cm';
            }
            return `${this._logoFooterPosition.top}cm`;
        },
        get absolutePositionImageLeft() {
            const left = roundToOneDecimal(
                this._logoFooterPosition.left +
                    this._elementSize('footer').width / 2 -
                    this._elementSize('logoFooter').width / 2,
            );

            return `${left}cm`;
        },
        get relativePositionImageLeft() {
            return `${this._logoFooterPosition.left}cm`;
        },
        get logoFooterHeight() {
            if (this._minFooterHeight !== null) {
                return `${this._minFooterHeight}cm`;
            } else {
                return 'auto';
            }
        },
        get logoFooterSize() {
            return this._elementSize('logoFooter');
        },
        get clientFooterSize() {
            return this._elementSize('client');
        },
        _elementSize(name) {
            if (this.$refs[name] === undefined) {
                throw new Error(`${name} reference is not defined`);
            }
            const { width, height } = this.$refs[name].getBoundingClientRect();
            return {
                width: roundToTwoDecimal(width / parent.pxPerCm),
                height: roundToTwoDecimal(height / parent.pyPerCm),
            };
        },
    };
};
