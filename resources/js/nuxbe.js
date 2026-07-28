import * as format from './nuxbe/format.js';
import { parseNumber, openDetailModal } from './nuxbe/utils.js';
import { lightbox, openLightbox } from './nuxbe/lightbox.js';
import './nuxbe/lightbox/handlers/image.js';
import './nuxbe/lightbox/handlers/pdf.js';
import './nuxbe/lightbox/handlers/video.js';
import './nuxbe/lightbox/handlers/audio.js';
import './nuxbe/lightbox/handlers/fallback.js';

// TallStackUI removes the dialog from the DOM before the confirm callback runs,
// so inputs rendered inside it are gone by the time a wire:click expression
// calls promptValue(). Snapshot every input the moment the dialog is accepted
// and fall back to that snapshot when the element no longer exists.
let promptValueStash = {};

if (typeof window !== 'undefined') {
    window.addEventListener('dialog:accepted', () => {
        promptValueStash = {};

        document
            .querySelectorAll('input[id], textarea[id], select[id]')
            .forEach((el) => {
                promptValueStash[el.id] =
                    el.type === 'checkbox' ? el.checked : el.value;
            });
    });
}

function promptValue(id) {
    const key = id ? id : 'prompt-value';
    const el = document.getElementById(key);

    if (!el) {
        return promptValueStash[key];
    }

    if (el.type === 'checkbox') {
        return el.checked;
    }

    return el.value;
}

let appModeCache = null;

function isAppMode() {
    if (appModeCache !== null) {
        return appModeCache;
    }

    if (typeof window === 'undefined') {
        return false;
    }

    appModeCache =
        window.matchMedia?.('(display-mode: standalone)')?.matches === true ||
        window.navigator?.standalone === true ||
        (window.nuxbeAppBridge?.isNative?.() ?? false);

    return appModeCache;
}

const nuxbe = {
    format,
    parseNumber,
    openDetailModal,
    promptValue,
    isAppMode,
    openLightbox,
    lightbox,
};

window.$nuxbe = nuxbe;

export default function (Alpine) {
    Alpine.magic('nuxbe', () => nuxbe);
}
