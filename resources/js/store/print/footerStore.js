import baseStore from './baseStore.js';
import {
    intersectionHandlerFactory,
    // roundToTwoDecimals,
    roundToTwoDecimals,
    STEP,
} from '../../components/utils/print/utils.js';
import PrintElement from '../../components/print/printElement.js';
import TemporaryMediaElement from '../../components/print/temporaryMediaElement.js';
import MediaElement from '../../components/print/mediaElement.js';
import TemporarySnippetBoxElement from '../../components/print/temporarySnippetBoxElement.js';
import SnippetBoxElement from '../../components/print/snippetBoxElement.js';

export default function () {
    return {
        ...baseStore(),
        footer: null,
        _footerHeight: 1.7, // cm
        _minFooterHeight: 1.7, // cm
        _maxFooterHeight: 5, // cm
        isFooterClicked: false,
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
        // TODO: diactivate on edit false
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
                        roundToTwoDecimals(
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
        onMouseMoveScale(e) {
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
            } else {
                throw new Error(`Element not selected - scale`);
            }
        },
        onMouseMoveResize(e) {
            if (this._selectedElement.ref !== null) {
                const deltaX = e.clientX - this._selectedElement.startX;
                const deltaY = e.clientY - this._selectedElement.startY;
                const { startHeight, startWidth } =
                    this._startSizeOfSelectedElement();
                if (deltaX >= 0 && deltaY >= 0) {
                    // TODO: this.footer.offsetWidth cache it in a variable - and restore on margin change
                    const maxWidth = this.footer.offsetWidth;
                    const maxHeight = this._footerHeight * this.pyPerCm;
                    const newHeight = startHeight + deltaY;
                    const newWidth = startWidth + deltaX;
                    if (newHeight < maxHeight && newWidth < maxWidth) {
                        this._selectedElement.ref.height = newHeight;
                        this._selectedElement.ref.width = newWidth;
                        this._selectedElement.height = newHeight;
                        this._selectedElement.width = newWidth;
                    }
                } else if (deltaX >= 0 && deltaY <= 0) {
                    const maxWidth = this.footer.offsetWidth;
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
                    const maxHeight = this._footerHeight * this.pyPerCm;
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
        get selectedElementId() {
            return this._selectedElement.id;
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

                this.visibleMedia.forEach((e) => {
                    this.observer.observe(e.element);
                });

                this.visibleSnippetBoxes.forEach((e) => {
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

            json.media?.forEach((item) => {
                const element = $refs['footer-media']?.content.cloneNode(true);
                if (element) {
                    this.footer.appendChild(element);
                    const children = Array.from(this.footer.children);
                    const index = children.findIndex(
                        (el) => el.id === 'footer-media',
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
                    $refs['footer-snippet']?.content.cloneNode(true);
                if (element) {
                    this.footer.appendChild(element);
                    const children = Array.from(this.footer.children);
                    const index = children.findIndex(
                        (el) => el.id === 'footer-snippet',
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
                    roundToTwoDecimals((element.height || 0) / this.pyPerCm),
                );
            } else {
                resizableElementHeights.push(0);
            }

            // temp media height
            const tempVisibleMedia = this.temporaryVisibleMedia.map((item) => {
                return roundToTwoDecimals((item.height || 0) / this.pyPerCm);
            });

            if (tempVisibleMedia.length > 0) {
                resizableElementHeights.push(...tempVisibleMedia);
            } else {
                resizableElementHeights.push(0);
            }

            // media
            const visibleMedia = this.visibleMedia.map((item) => {
                return roundToTwoDecimals((item.height || 0) / this.pyPerCm);
            });
            visibleMedia.length > 0
                ? resizableElementHeights.push(...visibleMedia)
                : resizableElementHeights.push(0);

            // temp snippet boxes height
            const tempSnippetBoxes = this.temporarySnippetBoxes.map((item) => {
                return roundToTwoDecimals((item.height || 0) / this.pyPerCm);
            });

            tempSnippetBoxes.length > 0
                ? resizableElementHeights.push(...tempSnippetBoxes)
                : resizableElementHeights.push(0);

            const visibleSnippetBoxes = this.visibleSnippetBoxes.map((item) => {
                return roundToTwoDecimals((item.height || 0) / this.pyPerCm);
            });

            visibleSnippetBoxes.length > 0
                ? resizableElementHeights.push(...visibleSnippetBoxes)
                : resizableElementHeights.push(0);

            return Math.max(...resizableElementHeights);
        },
        async reload($refs, isClientChange = true) {
            if (this.observer) {
                this.observer.disconnect();
            }

            // if client is not changed - Livewire will not remove the cloned elements
            if (!isClientChange) {
                this.visibleElements.forEach((item) => {
                    this.footer.removeChild(item.element);
                });
                this.temporaryVisibleMedia.forEach((item) => {
                    this.footer.removeChild(item.element);
                });
                this.visibleMedia.forEach((item) => {
                    this.footer.removeChild(item.element);
                });

                this.temporarySnippetBoxes.forEach((item) => {
                    this.footer.removeChild(item.element);
                });

                this.visibleSnippetBoxes.forEach((item) => {
                    this.footer.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];
            this.temporaryVisibleMedia = [];
            this.visibleMedia = [];
            this.temporarySnippetBoxes = [];
            this.visibleSnippetBoxes = [];

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

                this.visibleMedia.forEach((e) => {
                    this.observer.observe(e.element);
                });

                this.visibleSnippetBoxes.forEach((e) => {
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
        addToTemporarySnippet($refs) {
            if (this.footer !== null) {
                const cloneSnippetElement =
                    $refs['footer-additional-snippet']?.content.cloneNode(true);
                if (cloneSnippetElement) {
                    this.footer.appendChild(cloneSnippetElement);
                    const children = Array.from(this.footer.children);
                    const index = children.findIndex(
                        (item) => item.id === 'footer-snippet-placeholder',
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
                            'Footer additional snippet placeholder not found',
                        );
                    }
                } else {
                    throw new Error('Footer additional snippet not found');
                }
            }
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
        deleteMedia(id) {
            const index = this.visibleMedia.findIndex((item) => item.id === id);
            if (index !== -1) {
                this.observer.unobserve(this.visibleMedia[index].element);
                this.footer.removeChild(this.visibleMedia[index].element);
                this.visibleMedia.splice(index, 1);
            } else {
                throw new Error(`Media with id ${id} not found`);
            }
        },
        deleteSnippet(id) {
            const indexTemp = this.temporarySnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (indexTemp !== -1) {
                const obj = this.temporarySnippetBoxes[indexTemp];
                this.observer.unobserve(obj.element);
                this.footer.removeChild(obj.element);
                this.temporarySnippetBoxes.splice(indexTemp, 1);
            }

            const indexSnippet = this.visibleSnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (indexSnippet !== -1) {
                const obj = this.visibleSnippetBoxes[indexSnippet];
                this.observer.unobserve(obj.element);
                this.footer.removeChild(obj.element);
                this.visibleSnippetBoxes.splice(indexSnippet, 1);
            }

            if (indexTemp === -1 && indexSnippet === -1) {
                throw new Error(
                    `Snippet box or Temporary Snippet box with id ${id} not found`,
                );
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
                    const footerSnippets = this.temporarySnippetBoxes.map(
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
                        { footer: footerSnippets },
                        false,
                    );
                }

                return {
                    height: this._footerHeight,
                    elements: this.visibleElements.map((item) => {
                        return {
                            id: item.id,
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
                    }),
                    media: this.visibleMedia.map((item) => {
                        return {
                            id: item.mediaId,
                            src: item.src,
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
                    }),
                    temporaryMedia:
                        this.temporaryVisibleMedia.length > 0
                            ? this.temporaryVisibleMedia.map((item) => {
                                  return {
                                      name: item.temporaryFileName,
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
