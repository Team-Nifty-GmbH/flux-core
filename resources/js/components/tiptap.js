import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Mention from '@tiptap/extension-mention';
import axios from 'axios';

export default function (content, debounceDelay = 0, searchModel = ['user', 'role']) {
    return (() => {
        let _editor;
        let suggestionPopup;

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
                    extensions: [
                        StarterKit,
                        Mention.configure({
                            HTMLAttributes: { 'class': 'mention' },
                            suggestion: {
                                items: async ({ query }) => {
                                    return (await Promise.all(searchModel.map(async model => {
                                        return (await axios.get(`/search/${model}?search=${query}`)).data.map(item => {
                                            return {
                                                id: model + ':' + item.id,
                                                label: item.label,
                                                src: item.src,
                                            };
                                        });
                                    }))).flat();
                                },

                                render: () => {
                                    let suggestionElement = document.createElement('div');
                                    suggestionElement.className = 'suggestion-popup';

                                    return {
                                        onStart: props => {
                                            suggestionPopup = window.tippy(element, {
                                                content: suggestionElement,
                                                showOnCreate: true,
                                                interactive: true,
                                                trigger: 'manual',
                                                placement: 'bottom-start',
                                            });

                                            this.updateSuggestionItems(suggestionElement, props);
                                        },

                                        onUpdate: props => {
                                            if (!props.clientRect) {
                                                return;
                                            }

                                            this.updateSuggestionItems(suggestionElement, props);

                                            suggestionPopup.setProps({
                                                getReferenceClientRect: props.clientRect,
                                            });
                                        },

                                        onKeyDown: props => {
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
                        }),
                    ],
                    timeout: null,
                    content: this.content,
                    editable: this.editable,
                    editorProps: {
                        attributes: {
                            class: 'prose prose-sm max-w-full content-editable-placeholder placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block min-h-[85px] w-full rounded-b-md border p-3 shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm',
                        },
                    },
                    onUpdate: ({ editor }) => {
                        clearTimeout(this.timeout);
                        this.timeout = setTimeout(() => {
                            this.content = editor.getHTML();
                        }, debounceDelay);
                    },
                });

                this.proxy = Alpine.raw(_editor);

                this.$watch('editable', (editable) => {
                    this.proxy.setOptions({ editable: editable });
                });

                this.$watch('content', (content) => {
                    if (content === this.editor().getHTML()) return;
                    this.editor().commands.setContent(content, false);
                });
            },
            updateSuggestionItems(element, props) {
                while (element.firstChild) {
                    element.removeChild(element.firstChild);
                }

                props.items.forEach(item => {
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
            },
        };
    })();
}
