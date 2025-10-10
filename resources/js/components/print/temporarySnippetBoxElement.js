import { v4 as uuidv4 } from 'uuid';
import PrintElement from './printElement.js';

export default class TemporarySnippetBoxElement extends PrintElement {
    constructor(element, $store) {
        super(element, $store);
        this.id = `snippetbox-${uuidv4()}`;
        this._content = '';
        this._name = '';
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

    get content() {
        return this._content;
    }

    set content(value) {
        if (typeof value !== 'string') {
            throw new Error('Content must be a string');
        }

        this._content = value;
    }

    get name() {
        return this._name;
    }

    set name(value) {
        if (typeof value !== 'string') {
            throw new Error('Name must be a string');
        }
        this._name = value;
    }
}
