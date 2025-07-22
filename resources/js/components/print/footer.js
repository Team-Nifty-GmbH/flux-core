import { STEP } from '../utils/print/utils.js';

export default function (parent, $store) {
    return {
        _footerHeight: 1.7,
        _minFooterHeight: 1.7,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        startPointFooterVertical: null,
        _startPointClient: null,
        _startPointLogoFooter: null,
        _startPointBank: null,
        onMouseDownFooter(e, element) {
            if (element === 'footer') {
                this.isFooterClicked = true;
                this.startPointFooterVertical = e.clientY;
            }
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
                if (Math.abs(delta) >= STEP) {
                    const newHeight = Math.max(
                        0,
                        Math.round(
                            (this._footerHeight + STEP * (delta > 0 ? 1 : -1)) *
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
        get footerHeight() {
            return `${this._footerHeight}cm`;
        },
    };
}
