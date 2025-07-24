import footerStore from './store/print/footerStore.js';
import printStore from './store/print/printStore.js';
import { roundToOneDecimal } from './components/utils/print/utils.js';

// IMPORTANT: if store methods are passed as reference to x-on directives,
// this keyword will not refer to the store instance - hence they need to be called when passed to x-on directives

// x-data method gets correct this keyword by default, so it can be used directly in x-on directives

window.roundToOneDecimal = roundToOneDecimal;

document.addEventListener('alpine:init', () => {
    window.Alpine.store('footerStore', footerStore());
    window.Alpine.store(
        'printStore',
        printStore(window.Alpine.store('footerStore')),
    );
});
