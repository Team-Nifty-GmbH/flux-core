export default function ($store, id) {
    return {
        elementObj: null,
        text: '',
        onInit() {
            const index = $store.temporarySnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                this.elementObj = $store.temporarySnippetBoxes[index];
            } else {
                throw new Error('Template Snippet Element not found');
            }
        },
        toggleEditor() {
            $store.setSnippetEditorXDataRef(this);
        },
        saveText() {
            this.elementObj.content = this.text;
        },
        resetContent() {
            this.text = this.elementObj.content;
        },
    };
}
