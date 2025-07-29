import {
    intersectionHandlerFactory,
    roundToOneDecimal,
    STEP,
} from '../../components/utils/print/utils.js';
import PrintElement from '../../components/print/printElement.js';

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
        elemntsOutOfView: [],
        _component: null,
        footer: null,
        _footerHeight: 1.7,
        _minFooterHeight: 1.7,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        isImgResizeClicked: false,
        startPointFooterVertical: null,
        onInit(pxPerCm, pyPerCm) {
            if (typeof pyPerCm === 'number' && pyPerCm > 0) {
                this.pyPerCm = pyPerCm;
            } else {
                this.pyPerCm = 37.79527559055118; // 1cm in pixels, based on 96 DPI
            }

            if (typeof pxPerCm === 'number' && pxPerCm > 0) {
                this.pxPerCm = pxPerCm;
            } else {
                this.pxPerCm = 37.79527559055118; // 1cm in pixels, based on 96 DPI
            }
        },
        get component() {
            if (this._component === null) {
                throw new Error('Component not initialized');
            }

            return this._component();
        },
        onMouseDownFooter(e) {
            this.isFooterClicked = true;
            this.startPointFooterVertical = e.clientY;
        },
        onMouseUpFooter() {
            if (this.isFooterClicked) {
                this.isFooterClicked = false;
                this.startPointFooterVertical = null;

                this.repositionOnMouseUp();
            }
        },
        repositionOnMouseUp() {
            if (this.elemntsOutOfView.length > 0) {
                this.visibleElements
                    .filter((item) => this.elemntsOutOfView.includes(item.id))
                    .forEach((element) => {
                        element.positionBackInBound();
                    });
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
                        roundToOneDecimal(
                            this._footerHeight + STEP * (delta > 0 ? 1 : -1),
                        ),
                    );
                    // take in the account the resized logo size
                    if (
                        newHeight >=
                            Math.max(
                                this._adjustedMinFooterHeight(),
                                this._minFooterHeight,
                            ) &&
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
            if (
                this._selectedElement.x !== null &&
                this._selectedElement.y !== null
            ) {
                return {
                    x: this._selectedElement.x,
                    y: this._selectedElement.y,
                };
            }
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
            this._selectElement(e, id);
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
                // add an element
                this.footer.appendChild($ref[id].content.cloneNode(true));

                const element = Array.from(this.footer.children)
                    .filter((item) => item.id === id)
                    .pop();

                if (element) {
                    this.visibleElements.push(new PrintElement(element, this));
                    this.observer.observe(element);
                } else {
                    throw new Error(
                        `Element with id ${id} not found in footer`,
                    );
                }
            }
        },
        // this method runs every time when mouse is released - hence no need to duplicate clean logic in onMouseUpResize
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
        onMouseDownResize(e, id) {
            if (!this.isImgResizeClicked && id === 'footer-logo') {
                this.isImgResizeClicked = true;
                this._selectElement(e, id);
            }
        },
        onMouseUpResize() {
            if (this.isImgResizeClicked) {
                this.isImgResizeClicked = false;
            }
        },
        onMouseMoveResize(e) {
            if (this._selectedElement.ref !== null) {
                const deltaY = e.clientY - this._selectedElement.startY;
                // resize between min and max height
                if (deltaY > 0) {
                    const maxHeight = this._footerHeight * this.pyPerCm;
                    const startHeight =
                        this._selectedElement.ref.height ??
                        this._selectedElement.ref.size.height;
                    const newHeight = startHeight + 1;
                    if (newHeight < maxHeight) {
                        const newWidth =
                            (this._selectedElement.ref.width ??
                                this._selectedElement.ref.size.width) *
                            (newHeight / startHeight);

                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                    }
                } else if (deltaY < 0) {
                    const minHeight =
                        (this._minFooterHeight - 1) * this.pyPerCm;
                    const startHeight =
                        this._selectedElement.ref.height ??
                        this._selectedElement.ref.size.height;
                    const newHeight = startHeight - 1;
                    if (newHeight > minHeight) {
                        const newWidth =
                            (this._selectedElement.ref.width ??
                                this._selectedElement.ref.size.width) *
                            (newHeight / startHeight);
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                    }
                }

                this._selectedElement.startY = e.clientY;
            }
        },
        async register($wire, $refs) {
            this._component = () => $wire;
            this.footer = $refs['footer'];
            // Check if footer already exists - by default empty array is returned
            const footerJson = await $wire.get('form.footer');

            if (
                !Array.isArray(footerJson) &&
                Object.keys(footerJson).length > 0
            ) {
                this._mapFooter($refs, footerJson);
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
                    .map((item) => new PrintElement(item, this));

                ['start', 'middle', 'end'].forEach((position, index) => {
                    this.visibleElements[index] &&
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
        _mapFooter($refs, json) {
            this._footerHeight = json.height ?? 1.7;
            json.elements?.forEach((item) => {
                const element =
                    $refs[item.id] && $refs[item.id].content.cloneNode(true);
                if (element) {
                    this.footer.appendChild(element);
                    const children = Array.from(this.footer.children);
                    const index = children.findIndex((el) => el.id === item.id);
                    if (index !== -1) {
                        const child = children[index];
                        this.visibleElements.push(
                            new PrintElement(child, this).init({
                                x: item.x * this.pxPerCm,
                                y: item.y * this.pyPerCm,
                                ...(item.width && {
                                    width: item.width * this.pxPerCm,
                                }),
                                ...(item.height && {
                                    height: item.height * this.pyPerCm,
                                }),
                            }),
                        );
                    }
                }
            });
        },
        _selectElement(e, id) {
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
        _adjustedMinFooterHeight() {
            // for now only logo is resized - if in future other elements will be resized, this method should be updated
            // to search for the heihest element and return its height
            const indexOfLogo = this.visibleElements.findIndex(
                (item) => item.id === 'footer-logo',
            );
            if (indexOfLogo !== -1) {
                const element = this.visibleElements[indexOfLogo];
                return roundToOneDecimal((element.height || 0) / this.pyPerCm);
            } else {
                return 0;
            }
        },
        async reload($refs, isClientChange = true) {
            if (this.observer) {
                this.observer.disconnect();
            }

            // if client is not chaged - Livewire will not remove the cloned elements
            if (!isClientChange) {
                this.visibleElements.forEach((item) => {
                    this.footer.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elemntsOutOfView = [];

            const footerJson = await this.component.get('form.footer');

            if (
                !Array.isArray(footerJson) &&
                Object.keys(footerJson).length > 0
            ) {
                this._mapFooter($refs, footerJson);
            } else {
                const data = await this.component.clientToJson();
                const clientId = data.client.id;
                const imgUrl = data.client.logo_small_url;
                const bankConnections = data.bank_connections;
                this._footerHeight = 1.7;
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
                    .map((item) => new PrintElement(item, this));

                ['start', 'middle', 'end'].forEach((position, index) => {
                    this.visibleElements[index] &&
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
        resizeElement(id) {
            if (id !== 'footer-logo') {
                throw new Error('Resize is only supported for footer-logo');
            }
        },
        prepareToSubmit() {
            return {
                height: this._footerHeight,
                elements: this.visibleElements.map((item) => {
                    return {
                        id: item.id,
                        x: roundToOneDecimal(item.position.x / this.pxPerCm),
                        y: roundToOneDecimal(item.position.y / this.pyPerCm),
                        width:
                            item.width !== null
                                ? roundToOneDecimal(item.width / this.pxPerCm)
                                : null,
                        height:
                            item.height !== null
                                ? roundToOneDecimal(item.height / this.pyPerCm)
                                : null,
                    };
                }),
            };
        },
    };
}
