import baseStore from './baseStore.js';
import {
    intersectionHandlerFactory,
    nextTick,
    roundToOneDecimal,
    STEP,
} from '../../components/utils/print/utils.js';
import PrintElement from '../../components/print/printElement.js';

export default function () {
    return {
        ...baseStore(),
        firsPageHeader: null,
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
            if (this.firsPageHeader === null) {
                throw new Error('First page header is empty');
            } else {
                const { width, height } =
                    this.firsPageHeader.getBoundingClientRect();
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
                        newHeight >= this._minFirstPageHeaderHeight &&
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
        async register($wire, $refs) {
            this._component = () => $wire;
            this.firsPageHeader = $refs['first-page-header'];

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
                    this.firsPageHeader.appendChild(
                        $refs[item].content.cloneNode(true),
                    );
                });

                this.visibleElements = Array.from(this.firsPageHeader.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                const { width: parentWidth, height: parentHeight } =
                    this.firstPageSize;

                this._initOnEmptyJson(elementIds, parentWidth);
            }

            if (this.firsPageHeader) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.firsPageHeader,
                        rootMargin: '0px',
                        threshold: 0.99,
                    },
                );
                this.visibleElements.forEach((e) => {
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
                    this.firsPageHeader.removeChild(item.element);
                });
            }

            this.visibleElements = [];
            this.elementsOutOfView = [];

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
                    this.firsPageHeader.appendChild(
                        $refs[item].content.cloneNode(true),
                    );
                });

                this.visibleElements = Array.from(this.firsPageHeader.children)
                    .filter((item) => item.id && true)
                    .map((item) => new PrintElement(item, this));

                const { width: parentWidth, height: parentHeight } =
                    this.firstPageSize;

                this._initOnEmptyJson(elementIds, parentWidth);
            }

            if (this.firsPageHeader) {
                this.observer = new IntersectionObserver(
                    intersectionHandlerFactory(this),
                    {
                        root: this.firsPageHeader,
                        rootMargin: '0px',
                        threshold: 0.99,
                    },
                );
                this.visibleElements.forEach((e) => {
                    this.observer.observe(e.element);
                });
            }
        },
        prepareToSubmit() {
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
                    this.firsPageHeader.appendChild(element);
                    const children = Array.from(this.firsPageHeader.children);
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
        },
    };
}
