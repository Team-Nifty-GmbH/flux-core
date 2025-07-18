import footerStore from './store/print/footerStore.js';
import printEditorMain from './components/print/main.js';
import printEditorHeader from './components/print/header.js';
import printEditorFooter from './components/print/footer.js';

window.Alpine.store('footerStore', footerStore());

window.printEditorMain = printEditorMain;
window.printEditorHeader = printEditorHeader;
window.printEditorFooter = printEditorFooter;
