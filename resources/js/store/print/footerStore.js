import {
    intersectionHandlerFactory,
    STEP,
} from '../../components/utils/print/utils.js';
import FooterElement from '../../components/print/footerChildElement.js';

export default function () {
    return {
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
        elemntsOutOfView: [],
        alignment: {
            horizontal: null,
            vertical: null,
        },
        component: null,
        footer: null,
        _footerHeight: 1.7,
        _minFooterHeight: 1.7,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        startPointFooterVertical: null,
        onInit(pyPerCm) {
            if (typeof pyPerCm === 'number' && pyPerCm > 0) {
                this.pyPerCm = pyPerCm;
            } else {
                this.pyPerCm = 38;
            }
        },
        onMouseDownFooter(e) {
            this.isFooterClicked = true;
            this.startPointFooterVertical = e.clientY;
        },
        onMouseUpFooter() {
            if (this.isFooterClicked) {
                this.isFooterClicked = false;
                this.startPointFooterVertical = null;

                if (this.elemntsOutOfView.length > 0) {
                    this.visibleElements
                        .filter((item) =>
                            this.elemntsOutOfView.includes(item.id),
                        )
                        .forEach((element) => {
                            element.positionBackInBound();
                        });
                }
            }
        },
        onMouseMoveFooter(e) {
            if (
                this.isFooterClicked &&
                this.startPointFooterVertical !== null
            ) {
                const delta =
                    (this.startPointFooterVertical - e.clientY) / this.pyPerCm;
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
        get selectedElementPos() {
            // returns the position of the selected element
            return { x: 0, y: 0 };
        },
        get selectedElementId() {
            return this._selectedElement.id;
        },
        get footerSize() {
            if (this.footer) {
                const { height, width } = this.footer.getBoundingClientRect();
                return { width, height };
            } else {
                return { width: 0, height: 0 };
            }
        },
        onMouseDown(e, id) {
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
        },
        toggleElement($ref, id) {
            if (this.footer === null) {
                throw new Error(`Footer Elelement not initialized`);
            }

            const index = this.visibleElements.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                // delete element
                // remove the observer for the element
                this.observer.unobserve(this.visibleElements[index].element);
                this.footer.removeChild(this.visibleElements[index].element);
                this.visibleElements.splice(index, 1);
            } else {
                // add element
                this.footer.appendChild($ref[id].content.cloneNode(true));

                const element = Array.from(this.footer.children)
                    .filter((item) => item.id === id)
                    .pop();

                if (element) {
                    this.visibleElements.push(new FooterElement(element, this));
                    this.observer.observe(element);
                } else {
                    throw new Error(
                        `Element with id ${id} not found in footer`,
                    );
                }
            }
        },
        onMouseUp() {
            if (
                this._selectedElement.id !== null &&
                this._selectedElement.ref !== null &&
                this.elemntsOutOfView.includes(this._selectedElement.id)
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
        async register($wire, $refs) {
            this.component = $wire;
            this.footer = $refs['footer'];
            // Check if footer already exists
            if ((await $wire.get('form.footer')).length > 0) {
                console.log('Footer already exists, loading...');
            } else {
                const data = await $wire.clientToJson();
                const clientId = data.client.id;
                const imgUrl = data.client.logo_small_url;
                const bankConnections = data.bank_connections;
                // clone client
                this.footer.appendChild(
                    $refs[`footer-client-${clientId}`].content.cloneNode(true),
                );
                // clone logo
                imgUrl &&
                    this.footer.appendChild(
                        $refs['footer-logo'].content.cloneNode(true),
                    );
                // clone first bank connection
                bankConnections.length > 0 &&
                    this.footer.appendChild(
                        $refs[
                            `footer-bank-${bankConnections[0].id}`
                        ].content.cloneNode(true),
                    );
                this.visibleElements = Array.from(this.footer.children)
                    .filter((item) => item.id && true)
                    .map((item) => new FooterElement(item, this));

                ['start', 'middle', 'end'].forEach((position, index) => {
                    this.visibleElements[index].init(position);
                });
            }

            if (this.footer) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.footer,
                        rootMargin: '0px',
                        threshold: 0.99,
                    },
                );
                this.visibleElements.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        async reloadOnClientChange($refs) {},
    };
}
