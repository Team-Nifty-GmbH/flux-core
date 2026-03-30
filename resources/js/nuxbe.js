import * as format from './nuxbe/format.js';
import { parseNumber, openDetailModal } from './nuxbe/utils.js';

const nuxbe = { format, parseNumber, openDetailModal };

window.$nuxbe = nuxbe;

export default function (Alpine) {
    Alpine.magic('nuxbe', () => nuxbe);
}
