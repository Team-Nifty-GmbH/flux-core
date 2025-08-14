export default function () {
    return {
        pxPerCm: 0,
        pyPerCm: 0,
        observer: null,
        _selectedElement: {
            id: null,
            // x and y are just for UI purposes
            x: null,
            y: null,
            ref: null,
            startX: null,
            startY: null,
        },
        visibleElements: [],
        temporaryVisibleMedia: [],
        elementsOutOfView: [],
        _component: null,
        onInit(pxPerCm, pyPerCm) {
            if (typeof pyPerCm === 'number' && pyPerCm > 0) {
                this.pyPerCm = pyPerCm;
            } else {
                this.pyPerCm = 37.79527559055118; // 1 cm in pixels, based on 96 DPI
            }

            if (typeof pxPerCm === 'number' && pxPerCm > 0) {
                this.pxPerCm = pxPerCm;
            } else {
                this.pxPerCm = 37.79527559055118; // 1 cm in pixels, based on 96 DPI
            }
        },
        _selectElement(e, id, source) {
            if (source === 'element') {
                const index = this.visibleElements.findIndex(
                    (item) => item.id === id,
                );
                if (index !== -1) {
                    this._selectedElement.id = id;
                    this._selectedElement.ref = this.visibleElements[index];
                    const { x, y } = this.visibleElements[index].position;
                    this._selectedElement.x = x;
                    this._selectedElement.y = y;
                    this._selectedElement.startX = e.clientX;
                    this._selectedElement.startY = e.clientY;
                } else {
                    throw new Error(`Element with id ${id} not found`);
                }
            } else if (source === 'temporary') {
                const index = this.temporaryVisibleMedia.findIndex(
                    (item) => item.id === id,
                );
                if (index !== -1) {
                    this._selectedElement.id = id;
                    this._selectedElement.ref =
                        this.temporaryVisibleMedia[index];
                    const { x, y } = this.temporaryVisibleMedia[index].position;
                    this._selectedElement.x = x;
                    this._selectedElement.y = y;
                    this._selectedElement.startX = e.clientX;
                    this._selectedElement.startY = e.clientY;
                } else {
                    throw new Error(
                        `Temporary element with id ${id} not found`,
                    );
                }
            } else {
                throw new Error(
                    `Invalid source: ${source} - eather 'element' or 'temporary'`,
                );
            }
        },
        onMouseMove(e) {
            if (this._selectedElement.ref) {
                const { x, y } = this._selectedElement.ref.position;
                const deltaX = e.clientX - this._selectedElement.startX;
                const deltaY = e.clientY - this._selectedElement.startY;
                this._selectedElement.ref.position = {
                    x: x + deltaX,
                    y: y + deltaY,
                };
                this._selectedElement.startX = e.clientX;
                this._selectedElement.startY = e.clientY;
            } else {
                throw new Error(`Element not selected`);
            }
        },
        onMouseUp() {
            if (
                this._selectedElement.id !== null &&
                this._selectedElement.ref !== null &&
                this.elementsOutOfView.includes(this._selectedElement.id)
            ) {
                this._selectedElement.ref.positionBackInBound();
            }
            this._selectedElement.id = null;
            this._selectedElement.ref = null;
            this._selectedElement.x = null;
            this._selectedElement.y = null;
            this._selectedElement.startX = null;
            this._selectedElement.startY = null;
        },
        onMouseDown(e, id, source = 'elements') {
            // source can be 'existing' or 'temporary' - temporary means that the element has uuid as an id
            // generated on front end - after submitting the form, the id will be replaced with the id from the backend
            // and the element will become an existing element
            this._selectElement(e, id, source);
        },
        repositionOnMouseUp() {
            if (this.elementsOutOfView.length > 0) {
                this.visibleElements
                    .filter((item) => this.elementsOutOfView.includes(item.id))
                    .forEach((element) => {
                        element.positionBackInBound();
                    });
            }
        },
    };
}
