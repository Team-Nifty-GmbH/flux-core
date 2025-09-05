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
            $store.setSnippetIdEdited(id);
        },
    };
}
