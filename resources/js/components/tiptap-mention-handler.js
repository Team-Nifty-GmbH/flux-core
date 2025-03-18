import axios from 'axios';
import Mention from '@tiptap/extension-mention';

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

        div.addEventListener('click', () => {
            props.command({ id: item.id, label: item.label });
            suggestionPopup.hide();
        });
        element.appendChild(div);
    });
}

export const MentionConfig = Mention.configure({
    HTMLAttributes: { class: 'mention' },
    suggestion: {
        items: async ({ query }) => {
            return (
                await Promise.all(
                    searchModel.map(async (model) => {
                        return (
                            await axios.get(`/search/${model}?search=${query}`)
                        ).data.map((item) => {
                            return {
                                id: model + ':' + item.id,
                                label: item.label,
                                src: item.src,
                            };
                        });
                    }),
                )
            ).flat();
        },

        render: () => {
            let suggestionElement = document.createElement('div');
            suggestionElement.className = 'suggestion-popup';

            return {
                onStart: (props) => {
                    suggestionPopup = window.tippy(element, {
                        content: suggestionElement,
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom-start',
                    });

                    updateSuggestionItems(suggestionElement, props);
                },

                onUpdate: (props) => {
                    if (!props.clientRect) {
                        return;
                    }

                    updateSuggestionItems(suggestionElement, props);

                    suggestionPopup.setProps({
                        getReferenceClientRect: props.clientRect,
                    });
                },

                onKeyDown: (props) => {
                    if (props.event.key === 'Escape') {
                        suggestionPopup.hide();
                        return true;
                    }

                    // Add custom key handling if needed
                    return false;
                },

                onExit: () => {
                    suggestionPopup.destroy();
                },
            };
        },
    },
});
