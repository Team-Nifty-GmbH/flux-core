import { v4 as uuidv4 } from 'uuid';
import PrintElement from './printElement.js';

export default class TemporaryMediaElement extends PrintElement {
    constructor(element, $store, base64) {
        super(element, $store);
        this.imgElement = null;
        this.id = `media-${uuidv4()}`;
        this.url = base64 || '';
    }

    set url(value) {
        if (this.imgElement === null) {
            this.imgElement = this.element.querySelector('img');
        }

        this.imgElement.src = value;
    }

    get src() {
        if (this.imgElement === null) {
            this.imgElement = this.element.querySelector('img');
        }

        return this.imgElement.src;
    }
}
