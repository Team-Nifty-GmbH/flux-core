import PrintElement from './printElement.js';

export default class SnippetBoxElement extends PrintElement {
    constructor(element, $store, id) {
        super(element, $store);
        this.id = `snippetbox-${id}`;
        this._snippetId = id;
        this._content = null;
    }

    get snippet() {
        if (this._content === null) {
            this._content = this.store.component.snippetToJson(this._snippetId);
            return this._content;
        } else {
            return this._content;
        }
    }

    set content(value) {
        if (typeof value !== 'string') {
            throw new Error('Content must be a string');
        }

        this._content = value;
    }

    get content() {
        return this._content;
    }

    set id(value) {
        if (typeof value !== 'string') {
            throw new Error('ID must be a string');
        }

        this.element.id = value;
    }

    get id() {
        return this.element.id;
    }

    get snippetId() {
        return this._snippetId;
    }
}
