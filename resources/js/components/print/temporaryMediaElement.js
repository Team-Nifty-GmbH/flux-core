import { v4 as uuidv4 } from 'uuid';
import PrintElement from './printElement.js';

export default class TemporaryMediaElement extends PrintElement {
    constructor(element, $store, base64) {
        super(element, $store);
        this.id = `media-${uuidv4()}`;
        this.url = base64 || '';
    }

    set url(value) {
        this.element.src = value;
    }

    get src() {
        return this.element.src;
    }
}
