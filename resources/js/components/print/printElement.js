export default class PrintElement {
    constructor(element, $store) {
        this.element = element;
        this._position = { x: 0, y: 0 };
        this.store = $store;
        // cash the size to avoid recalculating it multiple times (bad performance)
        this._elementSize = null;
        // only for resizable elements - otherwise leave at null
        this._height = null;
        this._width = null;
    }

    get id() {
        return this.element.id;
    }

    set id(value) {
        if (typeof value !== 'string') {
            throw new Error('ID must be a string');
        }

        if (this.element.id !== undefined) {
            throw new Error('ID is already set and cannot be changed');
        }

        this.element.id = value;
    }

    get size() {
        // since the size of an element doesent change, we can cache it
        if (this._elementSize !== null && this.typeOfElement !== 'resizable') {
            return this._elementSize;
        } else {
            const { width, height } = this.element.getBoundingClientRect();
            this._elementSize = { width, height };
            return this._elementSize;
        }
    }

    positionBackInBound() {
        const { x, y } = this.position;
        const { width: widthParent, height: heightParent } = this.parentSize;
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
                    y + heightElement > heightParent
                        ? heightParent - heightElement
                        : y,
            };
        }

        if (x >= 0 && y <= 0) {
            this.position = {
                x:
                    x + widthElement > widthParent
                        ? widthParent - widthElement
                        : x,
                y: 0,
            };
        }

        if (x >= 0 && y >= 0) {
            this.position = {
                x:
                    x + widthElement > widthParent
                        ? widthParent - widthElement
                        : x,
                y:
                    y + heightElement > heightParent
                        ? heightParent - heightElement
                        : y,
            };
        }
    }

    get parent() {
        return this.element.parentElement;
    }

    get typeOfElement() {
        return this.element.dataset.type ?? 'unknown';
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
            // if the element is out of bounds, reset it to
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

    set height(value) {
        if (this.typeOfElement !== 'resizable') {
            throw new Error('Height can only be set for resizable elements');
        }
        if (typeof value !== 'number') {
            throw new Error('Height must be a number');
        }

        this.element.style.height = `${value}px`;
        this._height = value;
    }

    set width(value) {
        if (this.typeOfElement !== 'resizable') {
            throw new Error('Width can only be set for resizable elements');
        }
        if (typeof value !== 'number') {
            throw new Error('Width must be a number');
        }

        this.element.style.width = `${value}px`;
        this._width = value;
    }

    get position() {
        return this._position;
    }

    get height() {
        return this._height;
    }

    get width() {
        return this._width;
    }

    // 'start | 'middle' | 'end'

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

            if (startPosition === 'bottom-start') {
                this.position = {
                    x: 0,
                    y: this.parentSize.height - this.size.height,
                };
            }
        } else {
            if (
                typeof startPosition === 'object' &&
                startPosition.x !== undefined &&
                startPosition.y !== undefined
            ) {
                this.position = {
                    x: startPosition.x,
                    y: startPosition.y,
                };
                startPosition.width && (this.width = startPosition.width);
                startPosition.height && (this.height = startPosition.height);
            } else {
                throw new Error(
                    'Start position must be a string or an object with x and y properties',
                );
            }
        }

        return this;
    }
}
