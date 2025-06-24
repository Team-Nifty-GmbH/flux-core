window.printEditor = function () {
    return {
        _marginTop: 2,
        _marginBottom: 2,
        _marginLeft: 2,
        _marginRight: 2,
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
        setHorizontalMargin(isLeft) {},
        setVerticalMargin(isTop) {},
    };
};
