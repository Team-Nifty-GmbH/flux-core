import axios from 'axios';
import Mention from '@tiptap/extension-mention';
import { mergeAttributes } from '@tiptap/core';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

let suggestionPopup = null;

function updateSuggestionItems(element, props, options = {}) {
    while (element.firstChild) {
        element.removeChild(element.firstChild);
    }

    props.items.forEach((item) => {
        const div = document.createElement('div');
        div.className = 'suggestion-item flex gap-2 items-center justify-start';

        if (item.kind === 'scope') {
            div.classList.add('suggestion-scope');

            const span = document.createElement('span');
            span.textContent = item.label;
            div.appendChild(span);

            div.addEventListener('mousedown', (event) => {
                event.preventDefault();
                props.editor
                    .chain()
                    .focus()
                    .insertContentAt(props.range.to, item.scopeKey + ':')
                    .run();
            });

            element.appendChild(div);

            return;
        }

        if (item.src) {
            const img = document.createElement('img');
            img.src = item.src;
            img.className = 'h-6 w-6 rounded-full';
            div.appendChild(img);
        }

        const span = document.createElement('span');
        span.textContent = item.label;
        div.appendChild(span);

        if (options.showTypeBadge && item.typeLabel) {
            const badge = document.createElement('span');
            badge.className =
                'ml-auto text-xs px-1.5 py-0.5 rounded bg-secondary-100 text-secondary-600 dark:bg-secondary-700 dark:text-secondary-300';
            badge.textContent = item.typeLabel;
            div.appendChild(badge);
        }

        div.addEventListener('mousedown', (event) => {
            event.preventDefault();
            props.command({
                id: item.id,
                label: item.label,
                mentionType: options.showTypeBadge ? item.typeLabel : null,
                mentionUrl: item.url ?? null,
            });
        });

        element.appendChild(div);
    });
}

const buildMentionExtension = (name, char, types, element) => {
    const showTypeBadge = types.length > 1;

    return Mention.extend({
        name,
        addAttributes() {
            return {
                ...this.parent?.(),
                mentionType: {
                    default: null,
                    parseHTML: (element) =>
                        element.getAttribute('data-mention-type'),
                    renderHTML: (attributes) =>
                        attributes.mentionType
                            ? { 'data-mention-type': attributes.mentionType }
                            : {},
                },
                mentionUrl: {
                    default: null,
                    parseHTML: (element) => element.getAttribute('href'),
                    renderHTML: () => ({}),
                },
            };
        },
        parseHTML() {
            return [
                { tag: `span[data-type="${this.name}"]` },
                { tag: `a[data-type="${this.name}"]` },
            ];
        },
    }).configure({
        HTMLAttributes: { class: 'mention' },
        renderHTML: ({ options, node }) => [
            'a',
            mergeAttributes(
                options.HTMLAttributes,
                node.attrs.mentionUrl ? { href: node.attrs.mentionUrl } : {},
            ),
            `${node.attrs.mentionSuggestionChar ?? ''}${node.attrs.label ?? node.attrs.id}`,
        ],
        suggestion: {
            char,
            items: async ({ query }) => {
                const { data } = await axios.post('/search/mentionable', {
                    q: query,
                    types,
                });

                return data.map((item) => {
                    if (item.kind === 'scope') {
                        return {
                            kind: 'scope',
                            scopeKey: item.scope_key,
                            label: item.label,
                        };
                    }

                    return {
                        kind: 'record',
                        id: item.token.replace(/^[@#]/, ''),
                        label: item.label,
                        typeLabel: item.type_label,
                        url: item.url,
                        src: null,
                    };
                });
            },

            render: () => {
                let suggestionElement = document.createElement('div');
                suggestionElement.className =
                    'suggestion-popup absolute z-50 bg-white dark:bg-secondary-800 rounded-md shadow-lg border border-secondary-200 dark:border-secondary-700 p-2';
                suggestionElement.style.display = 'none';
                let virtualReference = null;

                const updatePosition = async () => {
                    if (!virtualReference || !suggestionElement) return;

                    const { x, y } = await computePosition(
                        virtualReference,
                        suggestionElement,
                        {
                            placement: 'bottom-start',
                            middleware: [
                                offset(8),
                                flip(),
                                shift({ padding: 5 }),
                            ],
                        },
                    );

                    Object.assign(suggestionElement.style, {
                        left: `${x}px`,
                        top: `${y}px`,
                    });
                };

                return {
                    onStart: (props) => {
                        if (!suggestionPopup) {
                            element.parentElement.appendChild(
                                suggestionElement,
                            );
                            Alpine.initTree(suggestionElement);
                            suggestionPopup = suggestionElement;
                        }

                        virtualReference = {
                            getBoundingClientRect: props.clientRect,
                        };

                        suggestionElement.style.display = 'block';
                        updateSuggestionItems(suggestionElement, props, {
                            showTypeBadge,
                        });
                        updatePosition();
                    },

                    onUpdate: (props) => {
                        if (!props.clientRect) {
                            return;
                        }

                        updateSuggestionItems(suggestionElement, props, {
                            showTypeBadge,
                        });

                        virtualReference = {
                            getBoundingClientRect: props.clientRect,
                        };

                        updatePosition();
                    },

                    onKeyDown: (props) => {
                        if (props.event.key === 'Escape') {
                            if (suggestionElement) {
                                suggestionElement.style.display = 'none';
                            }

                            return true;
                        }

                        return false;
                    },

                    onExit: () => {
                        if (suggestionElement) {
                            suggestionElement.style.display = 'none';
                            suggestionElement.remove();
                            suggestionPopup = null;
                        }
                    },
                };
            },
        },
    });
};

export const UserMentionConfig = (element) =>
    buildMentionExtension('mention', '@', ['user'], element);

export const RecordMentionConfig = (recordTypes, element) =>
    buildMentionExtension('recordMention', '#', recordTypes, element);
