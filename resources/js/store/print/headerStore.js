import baseStore from './baseStore.js';
import PrintElement from '../../components/print/printElement.js';
import {
    intersectionHandlerFactory,
    roundToOneDecimal,
    STEP,
} from '../../components/utils/print/utils.js';

// spread operator ignores prototype chain - getters and setters will not be copied
export default function () {
    return {
        ...baseStore(),
        header: null,
        _headerHeight: 1.7,
        _minHeaderHeight: 1.7,
        _maxHeaderHeight: 5,
        isHeaderClicked: false,
        isImgResizeClicked: false,
        startPointHeaderVertical: null,
        get headerHeight() {
            return `${this._headerHeight}cm`;
        },
        get selectedElementId() {
            return this._selectedElement.id;
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
        get component() {
            if (this._component === null) {
                throw new Error('Component not initialized');
            }
            return this._component();
        },
        async register($wire, $refs) {
            this._component = () => $wire;
            this.header = $refs['header'];

            const headerJson = await $wire.get('form.header');
            if (
                !Array.isArray(headerJson) &&
                Object.keys(headerJson).length > 0
            ) {
                this._mapHeader($refs, headerJson);
            } else {
                const { client } = await $wire.clientToJson();
                const imgUrl = client.logo_small_url;

                this.header.appendChild(
                    $refs['header-subject'].content.cloneNode(true),
                );

                imgUrl &&
                    this.header.appendChild(
                        $refs['header-logo'].content.cloneNode(true),
                    );

                this.visibleElements = Array.from(this.header.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                ['start', 'end'].forEach((position, index) => {
                    this.visibleElements[index] &&
                        this.visibleElements[index].init(position);
                });
            }

            if (this.header) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.header,
                        rootMargin: '0px',
                        threshold: 0.99,
                    },
                );
                this.visibleElements.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        onMouseDownHeader(e) {
            this.isHeaderClicked = true;
            this.startPointHeaderVertical = e.clientY;
        },
        onMouseMoveHeader(e) {
            if (
                this.isHeaderClicked &&
                this.startPointHeaderVertical !== null
            ) {
                const delta =
                    (this.startPointHeaderVertical - e.clientY) / this.pyPerCm;
                if (Math.abs(delta) >= STEP) {
                    const newHeight = Math.max(
                        0,
                        roundToOneDecimal(
                            this._headerHeight + STEP * (delta > 0 ? -1 : 1),
                        ),
                    );

                    // take in the account the resized logo size
                    if (
                        newHeight >=
                            Math.max(
                                this._adjustedMinHeaderHeight(),
                                this._minHeaderHeight,
                            ) &&
                        newHeight <= this._maxHeaderHeight
                    ) {
                        this._headerHeight = newHeight;
                    } else {
                        return;
                    }
                    this.startPointHeaderVertical = e.clientY;
                }
            }
        },
        onMouseUpHeader(e) {
            if (this.isHeaderClicked) {
                this.isHeaderClicked = false;
                this.startPointHeaderVertical = null;

                this.repositionOnMouseUp();
            }
        },
        toggleElement($ref, id) {
            if (this.header === null) {
                throw new Error(`Footer Elelement not initialized`);
            }

            const index = this.visibleElements.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                // delete element
                // remove the observer for the element
                this.observer.unobserve(this.visibleElements[index].element);
                this.header.removeChild(this.visibleElements[index].element);
                this.visibleElements.splice(index, 1);
            } else {
                // add an element
                this.header.appendChild($ref[id].content.cloneNode(true));

                const element = Array.from(this.header.children)
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
        onMouseDownResize(e, id) {
            if (!this.isImgResizeClicked && id === 'header-logo') {
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
                    const maxHeight = this._headerHeight * this.pyPerCm;
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
                        (this._minHeaderHeight - 1) * this.pyPerCm;
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
        _adjustedMinHeaderHeight() {
            // for now only logo is resized - if in future other elements will be resized, this method should be updated
            // to search for the heihest element and return its height
            const indexOfLogo = this.visibleElements.findIndex(
                (item) => item.id === 'header-logo',
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
                    this.header.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];

            const headerJson = await this.component.get('form.header');

            if (
                !Array.isArray(headerJson) &&
                Object.keys(headerJson).length > 0
            ) {
                this._mapHeader($refs, headerJson);
            } else {
                const { client } = await this.component.clientToJson();
                const imgUrl = client.logo_small_url;

                this.header.appendChild(
                    $refs['header-subject'].content.cloneNode(true),
                );

                imgUrl &&
                    this.header.appendChild(
                        $refs['header-logo'].content.cloneNode(true),
                    );

                this.visibleElements = Array.from(this.header.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                ['start', 'end'].forEach((position, index) => {
                    this.visibleElements[index] &&
                        this.visibleElements[index].init(position);
                });
            }

            if (this.header) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.header,
                        rootMargin: '0px',
                        threshold: 0.99,
                    },
                );
                this.visibleElements.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        _mapHeader($refs, json) {
            this._headerHeight = json.height ?? 1.7;
            json.elements?.forEach((item) => {
                const element =
                    $refs[item.id] && $refs[item.id].content.cloneNode(true);
                if (element) {
                    this.header.appendChild(element);
                    const children = Array.from(this.header.children);
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
        prepareToSubmit() {
            return {
                height: this._headerHeight,
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
