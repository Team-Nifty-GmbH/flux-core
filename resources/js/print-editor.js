import setupEditor from './components/tiptap.js';
import headerStore from './store/print/headerStore.js';
import footerStore from './store/print/footerStore.js';
import printStore from './store/print/printStore.js';
import firstPageHeaderStore from './store/print/firstPageHeaderStore.js';
import temporarySnippetEditor from './components/print/temporarySnippetEditor.js';
import snippetEditor from './components/print/snippetEditor.js';
import { roundToOneDecimal } from './components/utils/print/utils.js';
import tippy from 'tippy.js';
import toastComponent from './components/tallstackui/toast.js';

// IMPORTANT: if store methods are passed as reference to x-on directives,
// this keyword will not refer to the store instance - hence they need to be called when passed to x-on directives

// x-data method gets correct this keyword by default, so it can be used directly in x-on directives

window.tippy = tippy;
window.roundToOneDecimal = roundToOneDecimal;
window.temporarySnippetEditor = temporarySnippetEditor;
window.snippetEditor = snippetEditor;
// tippy related
window.setupEditor = setupEditor;
// notification related
window.$tallstackuiToast = toastComponent;

document.addEventListener('alpine:init', () => {
    console.log('Alpine init - setting up stores - print editor');
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
