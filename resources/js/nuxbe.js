import * as format from './nuxbe/format.js';
import { parseNumber, openDetailModal } from './nuxbe/utils.js';

function promptValue(id) {
    const el = document.getElementById(id ? id : 'prompt-value');

    if (el.type === 'checkbox') {
        return el.checked;
    }

    return el.value;
}

const nuxbe = { format, parseNumber, openDetailModal, promptValue };

window.$nuxbe = nuxbe;

export default function (Alpine) {
    Alpine.magic('nuxbe', () => nuxbe);
}
