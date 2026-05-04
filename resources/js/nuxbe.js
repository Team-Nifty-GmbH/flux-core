import * as format from './nuxbe/format.js';
import { parseNumber, openDetailModal } from './nuxbe/utils.js';

function promptValue(id) {
    const el = document.getElementById(id ? id : 'prompt-value');

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
};

window.$nuxbe = nuxbe;

export default function (Alpine) {
    Alpine.magic('nuxbe', () => nuxbe);
}
