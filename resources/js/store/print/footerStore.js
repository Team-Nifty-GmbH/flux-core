import baseStore from './baseStore.js';
import {
    intersectionHandlerFactory,
    roundToOneDecimal,
    STEP,
} from '../../components/utils/print/utils.js';
import PrintElement from '../../components/print/printElement.js';
import TemporaryMediaElement from '../../components/print/temporaryMediaElement.js';

export default function () {
    return {
        ...baseStore(),
        footer: null,
        _footerHeight: 1.7,
        _minFooterHeight: 1.7,
        _maxFooterHeight: 5,
        isFooterClicked: false,
        isImgResizeClicked: false,
        startPointFooterVertical: null,
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
        onMouseDownResize(e, id, source = 'element') {
            if (!this.isImgResizeClicked) {
                this.isImgResizeClicked = true;
                this._selectElement(e, id, source);
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
        _adjustedMinFooterHeight() {
            // taking in account logo height, additional media (temp and saved) height, and free-text fields
            const resizableElementHeights = [];
            const indexOfLogo = this.visibleElements.findIndex(
                (item) => item.id === 'footer-logo',
            );
            // logo height
            if (indexOfLogo !== -1) {
                const element = this.visibleElements[indexOfLogo];
                resizableElementHeights.push(
                    roundToOneDecimal((element.height || 0) / this.pyPerCm),
                );
            } else {
                resizableElementHeights.push(0);
            }

            // temp media height
            const tempVisibleMedia = this.temporaryVisibleMedia.map((item) => {
                return roundToOneDecimal((item.height || 0) / this.pyPerCm);
            });

            if (tempVisibleMedia.length > 0) {
                resizableElementHeights.push(...tempVisibleMedia);
            } else {
                resizableElementHeights.push(0);
            }

            return Math.max(...resizableElementHeights);
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
                this.temporaryVisibleMedia.forEach((item) => {
                    this.footer.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];
            this.temporaryVisibleMedia = [];

            const footerJson = await this.component.get('form.footer');

            if (
                !Array.isArray(footerJson) &&
                Object.keys(footerJson).length > 0
            ) {
                this._mapFooter($refs, footerJson);
            } else {
                this._footerHeight = 1.7;
                const data = await this.component.clientToJson();
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
        async addToTemporaryMedia(event, $refs) {
            const file = event.target.files[0];
            if (file !== undefined && this.footer) {
                const cloneMediaElement =
                    $refs['footer-additional-img']?.content.cloneNode(true);
                if (cloneMediaElement) {
                    this.footer.appendChild(cloneMediaElement);
                    const children = Array.from(this.footer.children);
                    const index = children.findIndex(
                        (item) => item.id === 'footer-img-placeholder',
                    );
                    if (index !== -1 && this.observer) {
                        const element = children[index];
                        this.temporaryVisibleMedia.push(
                            new TemporaryMediaElement(element, this, file).init(
                                'start',
                            ),
                        );
                        this.observer.observe(element);
                    } else {
                        throw new Error(
                            'Footer additional image placeholder not found',
                        );
                    }
                } else {
                    throw new Error(
                        'Footer additional image template not found',
                    );
                }
            }
            // clear the input field - to allow the same file to be selected again
            event.target.value = '';
        },
        deleteTemporaryMedia(id) {
            const index = this.temporaryVisibleMedia.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                this.observer.unobserve(
                    this.temporaryVisibleMedia[index].element,
                );
                this.footer.removeChild(
                    this.temporaryVisibleMedia[index].element,
                );
                this.temporaryVisibleMedia.splice(index, 1);
            } else {
                throw new Error(`Temporary media with id ${id} not found`);
            }
        },
        async prepareToSubmit() {
            try {
                if (this.temporaryVisibleMedia.length > 0) {
                    for (const item of this.temporaryVisibleMedia) {
                        await item.upload(this.component);
                    }
                }
                return {
                    height: this._footerHeight,
                    elements: this.visibleElements.map((item) => {
                        return {
                            id: item.id,
                            x: roundToOneDecimal(
                                item.position.x / this.pxPerCm,
                            ),
                            y: roundToOneDecimal(
                                item.position.y / this.pyPerCm,
                            ),
                            width:
                                item.width !== null
                                    ? roundToOneDecimal(
                                          item.width / this.pxPerCm,
                                      )
                                    : null,
                            height:
                                item.height !== null
                                    ? roundToOneDecimal(
                                          item.height / this.pyPerCm,
                                      )
                                    : null,
                        };
                    }),
                    temporaryMedia:
                        this.temporaryVisibleMedia.length > 0
                            ? this.temporaryVisibleMedia.map((item) => {
                                  return {
                                      name: item.temporaryFileName,
                                      x: roundToOneDecimal(
                                          item.position.x / this.pxPerCm,
                                      ),
                                      y: roundToOneDecimal(
                                          item.position.y / this.pyPerCm,
                                      ),
                                      width:
                                          item.width !== null
                                              ? roundToOneDecimal(
                                                    item.width / this.pxPerCm,
                                                )
                                              : null,
                                      height:
                                          item.height !== null
                                              ? roundToOneDecimal(
                                                    item.height / this.pyPerCm,
                                                )
                                              : null,
                                  };
                              })
                            : null,
                };
            } catch (e) {
                throw new Error(
                    'Error preparing footer for submission: ' + e.message,
                );
            }
        },
    };
}
