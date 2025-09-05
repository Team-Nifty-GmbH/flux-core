import setupEditor from './components/tiptap.js';
import headerStore from './store/print/headerStore.js';
import footerStore from './store/print/footerStore.js';
import printStore from './store/print/printStore.js';
import firstPageHeaderStore from './store/print/firstPageHeaderStore.js';
import snippetEditor from './components/print/snippetEditor.js';
import { roundToOneDecimal } from './components/utils/print/utils.js';
import tippy from 'tippy.js';

// IMPORTANT: if store methods are passed as reference to x-on directives,
// this keyword will not refer to the store instance - hence they need to be called when passed to x-on directives

// x-data method gets correct this keyword by default, so it can be used directly in x-on directives

window.tippy = tippy;
window.roundToOneDecimal = roundToOneDecimal;
window.snippetEditor = snippetEditor;
window.setupEditor = setupEditor;

document.addEventListener('alpine:init', () => {
    window.Alpine.store('footerStore', footerStore());
    window.Alpine.store('headerStore', headerStore());
    window.Alpine.store('firstPageHeaderStore', firstPageHeaderStore());
    window.Alpine.store(
        'printStore',
        printStore(
            window.Alpine.store('headerStore'),
            window.Alpine.store('firstPageHeaderStore'),
            window.Alpine.store('footerStore'),
        ),
    );
});
