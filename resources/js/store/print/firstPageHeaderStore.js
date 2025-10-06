import baseStore from './baseStore.js';
import {
    intersectionHandlerFactory,
    nextTick,
    roundToOneDecimal, roundToTwoDecimals,
    STEP
} from '../../components/utils/print/utils.js';
import PrintElement from '../../components/print/printElement.js';
import MediaElement from '../../components/print/mediaElement.js';
import TemporaryMediaElement from '../../components/print/temporaryMediaElement.js';
import SnippetBoxElement from '../../components/print/snippetBoxElement.js';
import TemporarySnippetBoxElement from '../../components/print/temporarySnippetBoxElement.js';

export default function () {
    return {
        ...baseStore(),
        firstPageHeader: null,
        _minFirstPageHeaderHeight: 5,
        _maxFirstPageHeaderHeight: 12,
        _firstPageHeaderHeight: 7,
        isFirstPageHeaderClicked: false,
        startPointFirstPageHeaderVertical: null,
        get component() {
            if (this._component === null) {
                throw new Error('Component not initialized');
            }
            return this._component();
        },
        get height() {
            return `${this._firstPageHeaderHeight}cm`;
        },
        get firstPageSize() {
            if (this.firstPageHeader === null) {
                throw new Error('First page header is empty');
            } else {
                const { width, height } =
                    this.firstPageHeader.getBoundingClientRect();
                return { width, height };
            }
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
        get selectedElementSize() {
            // x and y are values on store - since reactive
            // observe changes on x and y of selected element to retrieve the size
            if (
                this._selectedElement.width === null ||
                this._selectedElement.height === null
            ) {
                return { width: 0, height: 0 };
            }

            return {
                width: this._selectedElement.width,
                height: this._selectedElement.height,
            };
        },
        onMouseDownFirstPageHeader(e) {
            this.isFirstPageHeaderClicked = true;
            this.startPointFirstPageHeaderVertical = e.clientY;
        },
        onMouseMoveFirstPageHeader(e) {
            if (
                this.isFirstPageHeaderClicked &&
                this.startPointFirstPageHeaderVertical !== null
            ) {
                const delta =
                    (this.startPointFirstPageHeaderVertical - e.clientY) /
                    this.pyPerCm;
                if (Math.abs(delta) >= STEP) {
                    const newHeight = Math.max(
                        0,
                        roundToOneDecimal(
                            this._firstPageHeaderHeight +
                                STEP * (delta > 0 ? -1 : 1),
                        ),
                    );
                    // take in the account the resized logo size
                    if (
                        newHeight >=
                            Math.max(
                                this._adjustedMinFirstPageHeaderHeight(),
                                this._minFirstPageHeaderHeight,
                            ) &&
                        newHeight <= this._maxFirstPageHeaderHeight
                    ) {
                        this._firstPageHeaderHeight = newHeight;
                    } else {
                        return;
                    }
                    this.startPointFirstPageHeaderVertical = e.clientY;
                }
            }
        },
        onMouseUpFirstPageHeader(e) {
            if (this.isFirstPageHeaderClicked) {
                this.isFirstPageHeaderClicked = false;
                this.startPointFirstPageHeaderVertical = null;

                this.repositionOnMouseUp();
            }
        },
        onMouseMoveScale(e) {
            if (this._selectedElement.ref !== null) {
                const deltaY = e.clientY - this._selectedElement.startY;
                // resize between min and max height
                if (deltaY > 0) {
                    const maxHeight =
                        this._firstPageHeaderHeight * this.pyPerCm;
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
                    const minHeight = this.pyPerCm;
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
            } else {
                throw new Error(`Element not selected`);
            }
        },
        onMouseMoveResize(e) {
            if (this._selectedElement.ref !== null) {
                const deltaX = e.clientX - this._selectedElement.startX;
                const deltaY = e.clientY - this._selectedElement.startY;
                const { startHeight, startWidth } =
                    this._startSizeOfSelectedElement();
                if (deltaX >= 0 && deltaY >= 0) {
                    const maxWidth = this.firstPageHeader.offsetWidth;
                    const maxHeight = this._firstPageHeaderHeight * this.pyPerCm;
                    const newHeight = startHeight + deltaY;
                    const newWidth = startWidth + deltaX;
                    if (newHeight < maxHeight && newWidth < maxWidth) {
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                        this._selectedElement.height = newHeight;
                        this._selectedElement.width = newWidth;
                    }
                } else if (deltaX >= 0 && deltaY <= 0) {
                    const maxWidth = this.firstPageHeader.offsetWidth;
                    const minHeight = 10 ; // 9px - smallest font size
                    const newHeight = startHeight + deltaY;
                    const newWidth = startWidth + deltaX;
                    if (newHeight > minHeight && newWidth < maxWidth) {
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                        this._selectedElement.height = newHeight;
                        this._selectedElement.width = newWidth;
                    }
                } else if (deltaX <= 0 && deltaY >= 0) {
                    const minWidth = 3 * this.pxPerCm;
                    const maxHeight = this._firstPageHeaderHeight * this.pyPerCm;
                    const newHeight = startHeight + deltaY;
                    const newWidth = startWidth + deltaX;
                    if (newHeight < maxHeight && newWidth > minWidth) {
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                        this._selectedElement.height = newHeight;
                        this._selectedElement.width = newWidth;
                    }
                } else if (deltaX <= 0 && deltaY <= 0) {
                    const minWidth = 3 * this.pxPerCm;
                    const minHeight = 10; // 9px - smallest font size in editor
                    const newHeight = startHeight + deltaY;
                    const newWidth = startWidth + deltaX;
                    if (newHeight > minHeight && newWidth > minWidth) {
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                        this._selectedElement.height = newHeight;
                        this._selectedElement.width = newWidth;
                    }
                }

                this._selectedElement.startX = e.clientX;
                this._selectedElement.startY = e.clientY;
            } else {
                throw new Error(`Element not selected - resize`);
            }
        },
        get isResizeOrScaleActive() {
            return this.isImgResizeClicked || this.isSnippetResizeClicked;
        },
        get snippetNames() {
            const savedNames = this.visibleSnippetBoxes.map((item) => {
                return { name: `box-${item.snippetId}`, ref: item };
            });

            const maxId = Math.max(
                ...this.visibleSnippetBoxes.map((item) => item.snippetId),
                0,
            );

            const temporaryNames = this.temporarySnippetBoxes.map(
                (item, index) => {
                    return { name: `box-${maxId + index + 1}`, ref: item };
                },
            );

            return [...savedNames, ...temporaryNames];
        },
        async register($wire, $refs) {
            this._component = () => $wire;
            this.firstPageHeader = $refs['first-page-header'];

            const firstPageHeader = await $wire.get('form.first_page_header');

            if (
                !Array.isArray(firstPageHeader) &&
                Object.keys(firstPageHeader).length > 0
            ) {
                this._mapFirstPageHeader($refs, firstPageHeader);
            } else {
                // element order is important - since they are relatively ordered to each other
                const elementIds = [
                    'first-page-header-client-name',
                    'first-page-header-postal-address-one-line',
                    'first-page-header-address',
                    'first-page-header-subject',
                    'first-page-header-right-block',
                ];

                elementIds.forEach((item) => {
                    this.firstPageHeader.appendChild(
                        $refs[item].content.cloneNode(true),
                    );
                });

                this.visibleElements = Array.from(this.firstPageHeader.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                const { width: parentWidth, height: parentHeight } =
                    this.firstPageSize;

                this._initOnEmptyJson(elementIds, parentWidth);
            }

            if (this.firstPageHeader) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.firstPageHeader,
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

                this.visibleSnippetBoxes.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        async reload($refs, isClientChange = true) {
            if (this.observer) {
                this.observer.disconnect();
            }

            // if client is not changed - Livewire will not remove the cloned elements
            if (!isClientChange) {
                this.visibleElements.forEach((item) => {
                    this.firstPageHeader.removeChild(item.element);
                });

                this.temporaryVisibleMedia.forEach((item) => {
                    this.firstPageHeader.removeChild(item.element);
                });
                this.visibleMedia.forEach((item) => {
                    this.firstPageHeader.removeChild(item.element);
                });

                this.temporarySnippetBoxes.forEach((item) => {
                    this.firstPageHeader.removeChild(item.element);
                });

                this.visibleSnippetBoxes.forEach((item) => {
                    this.firstPageHeader.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];
            this.temporaryVisibleMedia = [];
            this.visibleMedia = [];
            this.temporarySnippetBoxes = [];
            this.visibleSnippetBoxes = [];

            const firstPageHeader = await this.component.get(
                'form.first_page_header',
            );

            if (
                !Array.isArray(firstPageHeader) &&
                Object.keys(firstPageHeader).length > 0
            ) {
                this._mapFirstPageHeader($refs, firstPageHeader);
            } else {
                this._firstPageHeaderHeight = 7;
                // THIS IS IMPORTANT - js will update but the DOM not at same time
                // height will be from the previous state

                // IN OTHER STORES WE DON'T NEED nextTick, SINCE THERE FIRST THE  CLIENT RELATED DATA IS FETCHED
                // HENCE REST OF THE CODE IS EXECUTED ON THE NEXT EVENT LOOP CYCLE - WHERE DOM IS SYNCED
                await nextTick();

                // element order is important - since they are relatively ordered to each other
                const elementIds = [
                    'first-page-header-client-name',
                    'first-page-header-postal-address-one-line',
                    'first-page-header-address',
                    'first-page-header-subject',
                    'first-page-header-right-block',
                ];

                elementIds.forEach((item) => {
                    this.firstPageHeader.appendChild(
                        $refs[item].content.cloneNode(true),
                    );
                });

                this.visibleElements = Array.from(this.firstPageHeader.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                const { width: parentWidth, height: parentHeight } =
                    this.firstPageSize;

                this._initOnEmptyJson(elementIds, parentWidth);
            }

            if (this.firstPageHeader) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.firstPageHeader,
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

                this.visibleSnippetBoxes.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        toggleElement($ref, id) {
            if (this.firstPageHeader === null) {
                throw new Error(`First Page Header Element not initialized`);
            }

            const index = this.visibleElements.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                // delete element
                // remove the observer for the element
                this.observer.unobserve(this.visibleElements[index].element);
                this.firstPageHeader.removeChild(
                    this.visibleElements[index].element,
                );
                this.visibleElements.splice(index, 1);
            } else {
                // add an element
                this.firstPageHeader.appendChild(
                    $ref[id].content.cloneNode(true),
                );

                const element = Array.from(this.firstPageHeader.children)
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
        async prepareToSubmit() {
            try {
                if (this.temporaryVisibleMedia.length > 0) {
                    for (const item of this.temporaryVisibleMedia) {
                        await item.upload(this.component);
                    }
                }

                if (this.temporarySnippetBoxes.length > 0) {
                    const firstPageHeaderSnippets = this.temporarySnippetBoxes.map(
                        (item) => {
                            return {
                                content: item.content,
                                x: roundToTwoDecimals(
                                    item.position.x / this.pxPerCm,
                                ),
                                y: roundToTwoDecimals(
                                    item.position.y / this.pyPerCm,
                                ),
                                width:
                                    item.width !== null
                                        ? roundToTwoDecimals(
                                            item.width / this.pxPerCm,
                                        )
                                        : null,
                                height:
                                    item.height !== null
                                        ? roundToTwoDecimals(
                                            item.height / this.pyPerCm,
                                        )
                                        : null,
                            };
                        },
                    );

                    await this.component.set(
                        'form.temporary_snippets',
                        { first_page_header: firstPageHeaderSnippets },
                        false,
                    );
                }

                return {
                    height: this._firstPageHeaderHeight,
                    elements: this.visibleElements.map((item) => {
                        const { x, y } = item.position;
                        return {
                            id: item.id,
                            x: roundToOneDecimal(x / this.pxPerCm),
                            y: roundToOneDecimal(y / this.pyPerCm),
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
                    snippets:
                        this.visibleSnippetBoxes.length > 0
                            ? this.visibleSnippetBoxes.map((item) => {
                                return {
                                    id: item.snippetId,
                                    content: item.content,
                                    x: roundToTwoDecimals(
                                        item.position.x / this.pxPerCm,
                                    ),
                                    y: roundToTwoDecimals(
                                        item.position.y / this.pyPerCm,
                                    ),
                                    width:
                                        item.width !== null
                                            ? roundToTwoDecimals(
                                                item.width / this.pxPerCm,
                                            )
                                            : null,
                                    height:
                                        item.height !== null
                                            ? roundToTwoDecimals(
                                                item.height / this.pyPerCm,
                                            )
                                            : null,
                                };
                            })
                            : null
                };
            } catch (e) {
                throw new Error(
                    'Error preparing first-page-header for submission: ' +
                        e.message,
                );
            }
        },
        _initOnEmptyJson(elementIds, parentWidth) {
            elementIds.forEach((id) => {
                const index = this.visibleElements.findIndex(
                    (i) => i.id === id,
                );
                if (index > -1) {
                    const item = this.visibleElements[index];
                    if (item.id === 'first-page-header-subject') {
                        item.init('bottom-start');
                    }

                    if (item.id === 'first-page-header-client-name') {
                        const y = 30;
                        const x = parentWidth / 2 - item.size.width / 2;
                        item.position = { x, y };
                    }

                    if (
                        item.id === 'first-page-header-postal-address-one-line'
                    ) {
                        const x = 0;
                        const indexOfClientName =
                            this.visibleElements.findIndex(
                                (i) => i.id === 'first-page-header-client-name',
                            );
                        if (indexOfClientName > -1) {
                            const clientName =
                                this.visibleElements[indexOfClientName];
                            const y =
                                clientName.position.y +
                                clientName.size.height +
                                30;
                            item.position = { x, y };
                        } else {
                            item.position = { x, y: 50 };
                        }
                    }
                    if (item.id === 'first-page-header-address') {
                        const x = 0;
                        const indexOfPostalAddress =
                            this.visibleElements.findIndex(
                                (i) =>
                                    i.id ===
                                    'first-page-header-postal-address-one-line',
                            );
                        if (indexOfPostalAddress > -1) {
                            const postalAddress =
                                this.visibleElements[indexOfPostalAddress];
                            const y =
                                postalAddress.position.y +
                                postalAddress.size.height +
                                30;
                            item.position = { x, y };
                        } else {
                            item.position = { x, y: 70 };
                        }
                    }

                    if (item.id === 'first-page-header-right-block') {
                        const x = parentWidth - item.size.width;
                        const indexOfAddress = this.visibleElements.findIndex(
                            (i) =>
                                i.id ===
                                'first-page-header-postal-address-one-line',
                        );
                        if (indexOfAddress > -1) {
                            const address =
                                this.visibleElements[indexOfAddress];
                            const y =
                                address.position.y + address.size.height + 30;
                            item.position = { x, y };
                        } else {
                            item.position = { x, y: 50 };
                        }
                    }
                }
            });
        },
        _mapFirstPageHeader($refs, json) {
            this._firstPageHeaderHeight = json.height ?? 7;
            json.elements?.forEach((item) => {
                const element =
                    $refs[item.id] && $refs[item.id].content.cloneNode(true);
                if (element) {
                    this.firstPageHeader.appendChild(element);
                    const children = Array.from(this.firstPageHeader.children);
                    const index = children.findIndex((el) => el.id === item.id);
                    if (index !== -1) {
                        const child = children[index];
                        // TODO: batch all init calls - in separate for each loop, after json is parsed  - performance reasons
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
                const element =
                    $refs['first-page-header-media']?.content.cloneNode(true);
                if (element) {
                    this.firstPageHeader.appendChild(element);
                    const children = Array.from(this.firstPageHeader.children);
                    const index = children.findIndex(
                        (el) => el.id === 'first-page-header-media',
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

            json.snippets?.map((item) => {
                const element =
                    $refs['first-page-header-snippet']?.content.cloneNode(true);
                if (element) {
                    this.firstPageHeader.appendChild(element);
                    const children = Array.from(this.firstPageHeader.children);
                    const index = children.findIndex(
                        (el) => el.id === 'first-page-header-snippet',
                    );
                    if (index !== -1) {
                        const child = children[index];
                        this.visibleSnippetBoxes.push(
                            new SnippetBoxElement(child, this, item.id).init({
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
        addToTemporarySnippet($refs) {
            if (this.firstPageHeader !== null) {
                const cloneSnippetElement =
                    $refs['first-page-header-additional-snippet']?.content.cloneNode(true);
                if (cloneSnippetElement) {
                    this.firstPageHeader.appendChild(cloneSnippetElement);
                    const children = Array.from(this.firstPageHeader.children);
                    const index = children.findIndex(
                        (item) => item.id === 'first-page-header-snippet-placeholder',
                    );
                    if (index !== -1 && this.observer) {
                        const element = children[index];
                        this.temporarySnippetBoxes.push(
                            new TemporarySnippetBoxElement(element, this).init(
                                'start',
                            ),
                        );
                        this.observer.observe(element);
                    } else {
                        throw new Error(
                            'First Page Header additional snippet placeholder not found',
                        );
                    }
                } else {
                    throw new Error('First Page Header additional snippet not found');
                }
            }
        },
        deleteSnippet(id) {
            const indexTemp = this.temporarySnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (indexTemp !== -1) {
                const obj = this.temporarySnippetBoxes[indexTemp];
                this.observer.unobserve(obj.element);
                this.firstPageHeader.removeChild(obj.element);
                this.temporarySnippetBoxes.splice(indexTemp, 1);
            }

            const indexSnippet = this.visibleSnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (indexSnippet !== -1) {
                const obj = this.visibleSnippetBoxes[indexSnippet];
                this.observer.unobserve(obj.element);
                this.firstPageHeader.removeChild(obj.element);
                this.visibleSnippetBoxes.splice(indexSnippet, 1);
            }

            if (indexTemp === -1 && indexSnippet === -1) {
                throw new Error(
                    `First Page Header - Snippet box or Temporary Snippet box with id ${id} not found`,
                );
            }
        },
        _adjustedMinFirstPageHeaderHeight() {
            // taking in account logo height, additional media (temp and saved) height, and free-text fields
            const resizableElementHeights = [];

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

            // snippet boxes
            const visibleSnippetBoxes = this.visibleSnippetBoxes.map((item) => {
                return roundToTwoDecimals((item.height || 0) / this.pyPerCm);
            });

            visibleSnippetBoxes.length > 0
                ? resizableElementHeights.push(...visibleSnippetBoxes)
                : resizableElementHeights.push(0);

            return Math.max(...resizableElementHeights);
        },
        async addToTemporaryMedia(event, $refs) {
            const file = event.target.files[0];
            if (file !== undefined && this.firstPageHeader) {
                const cloneMediaElement =
                    $refs[
                        'first-page-header-additional-img'
                    ]?.content.cloneNode(true);
                if (cloneMediaElement) {
                    this.firstPageHeader.appendChild(cloneMediaElement);
                    const children = Array.from(this.firstPageHeader.children);
                    const index = children.findIndex(
                        (item) =>
                            item.id === 'first-page-header-img-placeholder',
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
                this.firstPageHeader.removeChild(
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
                this.firstPageHeader.removeChild(
                    this.visibleMedia[index].element,
                );
                this.visibleMedia.splice(index, 1);
            } else {
                throw new Error(`Media with id ${id} not found`);
            }
        },
    };
}
