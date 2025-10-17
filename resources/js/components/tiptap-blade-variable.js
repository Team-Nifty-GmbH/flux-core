import { Node, mergeAttributes } from '@tiptap/core';

export const BladeVariable = Node.create({
    name: 'bladeVariable',

    group: 'inline',

    inline: true,

    selectable: false,

    atom: true,

    addAttributes() {
        return {
            label: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-label'),
                renderHTML: (attributes) => {
                    if (!attributes.label) {
                        return {};
                    }

                    return {
                        'data-label': attributes.label,
                    };
                },
            },
            value: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-value'),
                renderHTML: (attributes) => {
                    if (!attributes.value) {
                        return {};
                    }

                    return {
                        'data-value': attributes.value,
                    };
                },
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-type="blade-variable"]',
            },
        ];
    },

    renderHTML({ node, HTMLAttributes }) {
        return [
            'span',
            mergeAttributes(
                { 'data-type': 'blade-variable' },
                this.options.HTMLAttributes,
                HTMLAttributes,
            ),
            this.options.renderLabel({
                options: this.options,
                node,
            }),
        ];
    },

    renderText({ node }) {
        return `{{ ${node.attrs.value} }}`;
    },

    addKeyboardShortcuts() {
        return {
            Backspace: () =>
                this.editor.commands.command(({ tr, state }) => {
                    let isBladeVariable = false;
                    const { selection } = state;
                    const { empty, anchor } = selection;

                    if (!empty) {
                        return false;
                    }

                    state.doc.nodesBetween(anchor - 1, anchor, (node, pos) => {
                        if (node.type.name === this.name) {
                            isBladeVariable = true;
                            tr.delete(pos, pos + node.nodeSize);

                            return false;
                        }
                    });

                    return isBladeVariable;
                }),
        };
    },
});

export const BladeVariableConfig = () => {
    return BladeVariable.configure({
        HTMLAttributes: {
            class: 'blade-variable',
        },
        renderLabel({ node }) {
            return `${node.attrs.label}`;
        },
    });
};
