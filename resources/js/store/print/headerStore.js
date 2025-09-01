import baseStore from './baseStore.js';
import PrintElement from '../../components/print/printElement.js';
import {
    intersectionHandlerFactory,
    roundToOneDecimal,
    STEP,
} from '../../components/utils/print/utils.js';
import MediaElement from '../../components/print/mediaElement.js';
import TemporaryMediaElement from '../../components/print/temporaryMediaElement.js';

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
                throw new Error(`Header Elelement not initialized`);
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
                        `Element with id ${id} not found in header`,
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
            // taking in account logo height, additional media (temp and saved) height, and free-text fields
            const resizableElementHeights = [];
            const indexOfLogo = this.visibleElements.findIndex(
                (item) => item.id === 'header-logo',
            );
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

            // media
            const visibleMedia = this.visibleMedia.map((item) => {
                return roundToOneDecimal((item.height || 0) / this.pyPerCm);
            });
            visibleMedia.length > 0
                ? resizableElementHeights.push(...visibleMedia)
                : resizableElementHeights.push(0);

            return Math.max(...resizableElementHeights);
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

                this.temporaryVisibleMedia.forEach((item) => {
                    this.header.removeChild(item.element);
                });
                this.visibleMedia.forEach((item) => {
                    this.header.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];
            this.temporaryVisibleMedia = [];
            this.visibleMedia = [];

            const headerJson = await this.component.get('form.header');

            if (
                !Array.isArray(headerJson) &&
                Object.keys(headerJson).length > 0
            ) {
                this._mapHeader($refs, headerJson);
            } else {
                this._headerHeight = this._minHeaderHeight;
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

                this.visibleMedia.forEach((e) => {
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

            json.media?.forEach((item) => {
                const element = $refs['header-media']?.content.cloneNode(true);
                if (element) {
                    this.header.appendChild(element);
                    const children = Array.from(this.header.children);
                    const index = children.findIndex(
                        (el) => el.id === 'header-media',
                    );
                    if (index !== -1) {
                        const child = children[index];
                        this.visibleMedia.push(
                            new MediaElement(
                                child,
                                this,
                                item.id,
                                item.src,
                            ).init({
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
        async addToTemporaryMedia(event, $refs) {
            const file = event.target.files[0];
            if (file !== undefined && this.header) {
                const cloneMediaElement =
                    $refs['header-additional-img']?.content.cloneNode(true);
                if (cloneMediaElement) {
                    this.header.appendChild(cloneMediaElement);
                    const children = Array.from(this.header.children);
                    const index = children.findIndex(
                        (item) => item.id === 'header-img-placeholder',
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
                            'Header additional image placeholder not found',
                        );
                    }
                } else {
                    throw new Error(
                        'Header additional image template not found',
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
                this.header.removeChild(
                    this.temporaryVisibleMedia[index].element,
                );
                this.temporaryVisibleMedia.splice(index, 1);
            } else {
                throw new Error(`Temporary media with id ${id} not found`);
            }
        },
        deleteMedia(id) {
            const index = this.visibleMedia.findIndex((item) => item.id === id);
            if (index !== -1) {
                this.observer.unobserve(this.visibleMedia[index].element);
                this.header.removeChild(this.visibleMedia[index].element);
                this.visibleMedia.splice(index, 1);
            } else {
                throw new Error(`Media with id ${id} not found`);
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
                    height: this._headerHeight,
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
                    media: this.visibleMedia.map((item) => {
                        return {
                            id: item.mediaId,
                            src: item.src,
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
                    'Error preparing header for submission: ' + e.message,
                );
            }
        },
    };
}
