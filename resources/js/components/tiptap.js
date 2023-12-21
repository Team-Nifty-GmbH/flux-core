import {Editor} from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";

export default function (content) {
    return (() => {
        let _editor;

        return {
            editor() {
                return _editor;
            },
            proxy: null,
            editable: true,
            content: content,
            init(element) {
                _editor = new Editor({
                    element: element,
                    extensions: [StarterKit],
                    content: this.content,
                    editable: this.editable,
                    editorProps: {
                        attributes: {
                            class: 'prose prose-sm max-w-full content-editable-placeholder placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block min-h-[85px] w-full rounded-b-md border p-3 shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm',
                        },
                    },
                    onUpdate: ({ editor }) => {
                        this.content = editor.getHTML();
                    }
                });
                this.proxy = Alpine.raw(_editor);
                this.$watch('editable', (editable) => {
                    this.proxy.setOptions({
                        editable: editable
                    });
                });
                this.$watch('content', (content) => {
                    if (content === this.editor().getHTML()) return
                    this.editor().commands.setContent(content, false)
                });
            }
        };
    })();
}
