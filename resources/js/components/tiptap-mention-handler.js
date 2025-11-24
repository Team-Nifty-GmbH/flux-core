import axios from 'axios';
import Mention from '@tiptap/extension-mention';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

let suggestionPopup = null;

function updateSuggestionItems(element, props) {
    while (element.firstChild) {
        element.removeChild(element.firstChild);
    }

    props.items.forEach((item) => {
        const div = document.createElement('div');
        div.className = 'suggestion-item flex gap-1 justify-start';
        const img = document.createElement('img');
        img.src = item.src;
        img.className = 'h-6 w-6 rounded-full';
        const span = document.createElement('span');
        span.textContent = item.label;

        div.appendChild(img);
        div.appendChild(span);

        div.addEventListener('mousedown', (event) => {
            event.preventDefault();
            props.command({ id: item.id, label: item.label });
        });

        element.appendChild(div);
    });
}

export const MentionConfig = (searchModel, element) =>
    Mention.configure({
        HTMLAttributes: { class: 'mention' },
        suggestion: {
            items: async ({ query }) => {
                return (
                    await Promise.all(
                        searchModel.map(async (model) => {
                            let data = {};
                            if (model === 'user') {
                                data = {
                                    where: [
                                        {
                                            column: 'is_active',
                                            operator: '=',
                                            value: true,
                                        },
                                    ],
                                };
                            }

                            return (
                                await axios.post(
                                    `/search/${model}?search=${query}`,
                                    data,
                                )
                            ).data.map((item) => {
                                return {
                                    id: model + ':' + item.id,
                                    label: item.label,
                                    src: item.image,
                                };
                            });
                        }),
                    )
                ).flat();
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
                        updateSuggestionItems(suggestionElement, props);
                        updatePosition();
                    },

                    onUpdate: (props) => {
                        if (!props.clientRect) {
                            return;
                        }

                        updateSuggestionItems(suggestionElement, props);

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
