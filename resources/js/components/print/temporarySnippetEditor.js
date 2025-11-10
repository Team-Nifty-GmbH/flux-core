export default function ($store, id) {
    return {
        get objId() {
            return this.elementObj && this.elementObj.id;
        },
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
