import baseStore from './baseStore.js';

// spread operator ignores prototype chain - getters and setters will not be copied
export default function () {
    return {
        ...baseStore(),
        get component() {
            if (this._component === null) {
                throw new Error('Component not initialized');
            }
            return this._component();
        },
        header: null,
        _headerHeight: 1.7,
        _minHeaderHeight: 1.7,
        _maxHeaderHeight: 5,
        isHeaderClicked: false,
        isImgResizeClicked: false,
        startPointHeaderVertical: null,
    };
}
