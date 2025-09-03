import PrintElement from './printElement.js';

export default class MediaElement extends PrintElement {
    constructor(element, $store, id, src) {
        super(element, $store);
        this._mediaId = id;
        this.id = `media-${id}`;
        this._imgElement = null;
        this.url = src;
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

    set url(value) {
        if (this._imgElement === null) {
            this._imgElement = this.element.querySelector('img');
        }
        this._imgElement.src = value;
    }

    get src() {
        if (this._imgElement === null) {
            this._imgElement = this.element.querySelector('img');
        }
        return this._imgElement.src;
    }

    get mediaId() {
        return this._mediaId;
    }
}
