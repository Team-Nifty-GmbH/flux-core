import {
    roundToOneDecimal,
    roundToTwoDecimal,
    STEP,
} from '../utils/print/utils.js';

export default function (parent) {
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
}
