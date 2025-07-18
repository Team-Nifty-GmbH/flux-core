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
    constructor(id, type, position, element) {
        this.id = id;
        this.type = type;
        this.position = position;
        this.element = element;
    }
}

export default function () {
    return {
        selectedElement: null,
        visibleElements: [],
        alignment: {
            horizontal: null,
            vertical: null,
        },
        component: null,
        async register($wire, $refs) {
            console.log(
                await $wire.clientToJson(),
                await $wire.get('form.footer'),
            );
        },
        async reloadOnClientChange($refs) {},
        toggleElement(element) {},
        selectElement(id) {},
    };
}
