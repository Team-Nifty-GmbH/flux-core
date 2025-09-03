import { v4 as uuidv4 } from 'uuid';
import PrintElement from './printElement.js';

export default class TemporaryMediaElement extends PrintElement {
    constructor(element, $store, file) {
        super(element, $store);
        this._imgFile = file;
        this._imgElement = null;
        // used in action to refer to file name on backend - before submiting it to db
        this._temporaryFileName = null;
        this.id = `media-${uuidv4()}`;
        this.url = file;
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

    set url(file) {
        if (this._imgElement === null) {
            this._imgElement = this.element.querySelector('img');
        }

        this._imgElement.src = URL.createObjectURL(file);
    }

    get src() {
        if (this._imgElement === null) {
            this._imgElement = this.element.querySelector('img');
        }

        return this._imgElement.src;
    }

    get temporaryFileName() {
        if (this._temporaryFileName === null) {
            throw new Error('Temporary file name is not set');
        }
        return this._temporaryFileName;
    }

    upload($component) {
        const $this = this;
        if (this._imgFile !== null) {
            return new Promise(async (resolve, reject) => {
                await $component.upload(
                    'form.temporaryMedia',
                    $this._imgFile,
                    (tempFileName) => {
                        $this._temporaryFileName = tempFileName;
                        // TODO: validate on demand
                        resolve();
                    },
                    (error) => {
                        reject(error);
                    },
                );
            });
        } else {
            throw new Error('Image file is not initialized');
        }
    }
}
