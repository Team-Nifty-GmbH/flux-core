import baseStore from './baseStore.js';
import {
    intersectionHandlerFactory,
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

                // this.repositionOnMouseUp();
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
                // this._mapHeader($refs, firstPageHeader);
            } else {
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

                // the order of the elements is important
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
                            item.id ===
                            'first-page-header-postal-address-one-line'
                        ) {
                            const x = 0;
                            const indexOfClientName =
                                this.visibleElements.findIndex(
                                    (i) =>
                                        i.id ===
                                        'first-page-header-client-name',
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
                    }
                });

                // this.visibleElements.forEach((item) => {
                //     if (item.id === 'first-page-header-client-name') {
                //         const y = 30;
                //         const x = parentWidth / 2 - item.size.width / 2;
                //         item.position = { x, y };
                //     }
                //
                //     if (item.id === 'first-page-header-subject') {
                //         item.init('bottom-start');
                //     }
                // });
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
    };
}
