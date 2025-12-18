import { TextAlign } from '@tiptap/extension-text-align';

export const TextAlignConfig = TextAlign.configure({
    types: ['heading', 'paragraph'],
    defaultAlignment: 'left',
    alignments: ['left', 'center', 'right'],
});
