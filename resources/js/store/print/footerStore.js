/**
 *
 * element properties:
 * @typedef { Object } Element
 * @property { string } id
 * @property { string } type - Typ des Elements (z.B. "text", "image", etc.)
 * @property {{ left: number, top: number }} position
 * @property {{ width: number, height: number }} size
 */

class FooterElement {
    constructor(element, $store) {
        this.element = element;
        this._position = { x: 0, y: 0 };
        this.store = $store;
    }

    get id() {
        console.log('get id', this.element.id);
        return this.element.id;
    }

    get size() {
        const { width, height } = this.element.getBoundingClientRect();
        return { width, height };
    }

    get parent() {
        return this.element.parentElement;
    }

    get parentSize() {
        if (this.parent) {
            const { width, height } = this.parent.getBoundingClientRect();
            return { width, height };
        } else {
            return { width: 0, height: 0 };
        }
    }

    set position(value) {
        if (
            typeof value === 'object' &&
            value.x !== undefined &&
            value.y !== undefined
        ) {
            this._position = { x: value.x, y: value.y };
            this.element.style.transform = `translate(${value.x}px,${value.y}px)`;
            // TODO: update store with new position on selected element - if selected
        } else {
            throw new Error(
                'Position must be an object with x and y properties',
            );
        }
    }

    // 'start | 'middle' | 'end' | 'random'

    init(startPosition) {
        if (typeof startPosition === 'string') {
            if (startPosition === 'middle') {
                this.position = {
                    x: (this.parentSize.width - this.size.width) / 2,
                    y: 0,
                };
            }

            if (startPosition === 'end') {
                console.log(this.size.width);
                this.position = {
                    x: this.parentSize.width - this.size.width,
                    y: 0,
                };
            }
        }
    }

    updatePosition(x, y) {}
}

export default function () {
    return {
        _selectedElement: { id: null, x: null, y: null },
        visibleElements: [],
        alignment: {
            horizontal: null,
            vertical: null,
        },
        component: null,
        footer: null,
        get selectedElementPos() {
            // returns the position of the selected element
            return { x: 0, y: 0 };
        },
        get footerSize() {
            if (this.footer) {
                const { height, width } = this.footer.getBoundingClientRect();
                return { width, height };
            } else {
                return { width: 0, height: 0 };
            }
        },
        onClick(e) {
            console.log(e.currentTarget);
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
        },
        async reloadOnClientChange($refs) {},
        toggleElement(element) {},
        selectElement(id) {},
    };
}
