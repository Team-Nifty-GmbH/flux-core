export default function ($store, id) {
    return {
        get objId() {
            return this.elementObj && this.elementObj.id;
        },
        elementObj: null,
        text: '',
        async onInit() {
            const index = $store.visibleSnippetBoxes.findIndex(
                (item) => item.id === id,
            );
            if (index !== -1) {
                this.elementObj = $store.visibleSnippetBoxes[index];
                this.text = await this.elementObj.snippet();
            } else {
                throw new Error('Snippet Element not found');
            }
        },
        toggleEditor() {
            $store.setSnippetEditorXDataRef(this);
        },
        saveText() {
            this.elementObj.content = this.text;
        },
        async resetContent() {
            this.text = await this.elementObj.snippet();
        },
    };
}
