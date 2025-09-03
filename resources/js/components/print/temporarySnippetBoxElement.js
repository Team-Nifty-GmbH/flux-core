import { v4 as uuidv4 } from 'uuid';
import PrintElement from './printElement.js';

export default class TemporarySnippetBoxElement extends PrintElement {
    constructor(element, $store) {
        super(element, $store);
        this.id = `snippetbox-${uuidv4()}`;
    }

    get id() {
        return this.element.id;
    }

    set id(value) {
        if (typeof value !== 'string') {
            throw new Error('ID must be a string');
        }

        this.element.id = value;
    }
}
