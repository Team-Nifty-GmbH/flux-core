export default class FooterChildElement {
    constructor(element, $store) {
        this.element = element;
        this._position = { x: 0, y: 0 };
        this.store = $store;
        this.startPosition = null;
        // cash the size to avoid recalculating it multiple times (bad performance)
        this._elementSize = null;
    }

    get id() {
        return this.element.id;
    }

    get size() {
        // since the size of an element doesent cange, we can cache it
        if (this._elementSize !== null) {
            return this._elementSize;
        } else {
            const { width, height } = this.element.getBoundingClientRect();
            this._elementSize = { width, height };
            return this._elementSize;
        }
    }

    positionBackInBound() {
        const { x, y } = this.position;
        const { width: widthFooter, height: heightFooter } = this.parentSize;
        const { width: widthElement, height: heightElement } = this.size;
        if (x <= 0 && y <= 0) {
            // Element is out of view, reset position
            this.position = {
                x: 0,
                y: 0,
            };
        }

        if (x <= 0 && y >= 0) {
            this.position = {
                x: 0,
                y:
                    y + heightElement > heightFooter
                        ? heightFooter - heightElement
                        : y,
            };
        }

        if (x >= 0 && y <= 0) {
            this.position = {
                x:
                    x + widthElement > widthFooter
                        ? widthFooter - widthElement
                        : x,
                y: 0,
            };
        }

        if (x >= 0 && y >= 0) {
            this.position = {
                x:
                    x + widthElement > widthFooter
                        ? widthFooter - widthElement
                        : x,
                y:
                    y + heightElement > heightFooter
                        ? heightFooter - heightElement
                        : y,
            };
        }
    }

    get parent() {
        return this.element.parentElement;
    }

    get parentSize() {
        if (this.parent) {
            const { width, height } = this.parent.getBoundingClientRect();
            return { width, height };
        } else {
            return { width: 0, height: 0 };
        }
    }

    set position(value) {
        if (
            typeof value === 'object' &&
            value.x !== undefined &&
            value.y !== undefined
        ) {
            this._position = { x: value.x, y: value.y };
            this.element.style.transform = `translate(${value.x}px,${value.y}px)`;
            // to display the element in the correct position in the footer
            if (this.store._selectedElement) {
                this.store._selectedElement.x = value.x;
                this.store._selectedElement.y = value.y;
            }
        } else {
            throw new Error(
                'Position must be an object with x and y properties',
            );
        }
    }

    get position() {
        return this._position;
    }

    // 'start | 'middle' | 'end' | 'coordinates'

    init(startPosition) {
        if (typeof startPosition === 'string') {
            if (startPosition === 'middle') {
                this.position = {
                    x: (this.parentSize.width - this.size.width) / 2,
                    y: 0,
                };
            }

            if (startPosition === 'end') {
                this.position = {
                    x: this.parentSize.width - this.size.width,
                    y: 0,
                };
            }

            this.startPosition = startPosition;
        } else {
        }
    }
}
