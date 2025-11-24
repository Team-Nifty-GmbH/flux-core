import Link from '@tiptap/extension-link';

export function linkHandler(editor) {
    return () => {
        if (editor === undefined) return;

        const previousUrl = editor.getAttributes('link').href || '';
        const url = window.prompt('URL', previousUrl);

        // cancelled
        if (url === null) {
            return;
        }

        // empty
        if (url === '') {
            editor.chain().focus().unsetLink().run();

            return;
        }

        // update link
        editor.chain().focus().setLink({ href: url }).run();
    };
}

export const LinkConfiguration = Link.configure({
    defaultProtocol: 'https',
    HTMLAttributes: {
        class: 'link-style',
        rel: 'noopener noreferrer',
        target: '_blank',
    },
});
