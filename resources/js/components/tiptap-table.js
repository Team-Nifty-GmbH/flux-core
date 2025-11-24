import { TableKit } from '@tiptap/extension-table';

export const Table = TableKit.configure({
    tableCell: {
        HTMLAttributes: {
            class: 'p-2',
        },
    },
    table: {
        resizable: true,
    },
});
