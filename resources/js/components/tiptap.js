import { Editor } from '@tiptap/core';
import { LiteralTab } from './tiptap-literal-tab-handler.js';
import { FontSizeColorConfig } from './tiptap-font-size-color-handler.js';
import StarterKit from '@tiptap/starter-kit';
import { MentionConfig } from './tiptap-mention-handler.js';

export default function (
    content,
    debounceDelay = 0,
    searchModel = ['user', 'role'],
) {
    return () => {
        let _editor;

        return {
            editor() {
                return _editor;
            },
            proxy: null,
            editable: true,
            content: content,
            popUp: null,
            isClickListenerSet: false,
            setIsClickListenerSet(value) {
                this.isClickListenerSet = value;
            },
            initTextArea(
                id,
                element,
                isTransparent,
                showTooltipDropdown,
                initFontSize,
            ) {
                const popUp = this.$refs[`popWindow-${id}`];
                const controlPanel = this.$refs[`controlPanel-${id}`];
                const commands = this.$refs[`commands-${id}`];
                let actions = null;

                if (showTooltipDropdown && popUp !== null) {
                    // append controllers to tiptap
                    const popUpNode = popUp.content.cloneNode(true);
                    const commandsNode = commands.content.cloneNode(true);
                    popUpNode.appendChild(commandsNode);
                    actions = popUpNode;
                } else {
                    // append to controls to div
                    controlPanel.appendChild(commands.content.cloneNode(true));
                }

                //  access to the parent scope in onSelectionUpdate callback
                const parent = this;
                // related to dropdown visibility
                const onClickHandler = function () {
                    parent.popUp.show();
                    parent.setIsClickListenerSet(false);
                };
                _editor = new Editor({
                    element: element,
                    extensions: [
                        StarterKit,
                        FontSizeColorConfig,
                        LiteralTab,
                        MentionConfig(searchModel, element),
                    ],
                    timeout: null,
                    content: this.content,
                    editable: this.editable,
                    editorProps: {
                        attributes: {
                            class: `${isTransparent ? 'bg-transparent' : 'dark:bg-secondary-800'} ${showTooltipDropdown ? 'rounded-md' : 'rounded-b-md'} \
                                prose prose-sm dark:prose-invert max-w-full content-editable-placeholder dark:text-gray-50 placeholder-secondary-400 dark:placeholder-secondary-500 \
                                border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block \
                                min-h-[85px] w-full border p-3 shadow-sm transition duration-100 ease-in-out focus:outline-none sm:text-sm`,
                            style: `${initFontSize !== null ? `font-size:${initFontSize}px` : ''}`,
                        },
                    },
                    // text selection handler
                    onSelectionUpdate: ({ editor }) => {
                        if (!showTooltipDropdown) {
                            return;
                        }

                        const { from, to } = editor.state.selection;
                        // init popUp if not
                        if (parent.popUp === null) {
                            parent.popUp = window.tippy(element, {
                                content: actions ?? 'not defined',
                                showOnCreate: true,
                                interactive: true,
                                trigger: 'manual',
                                placement: 'top',
                            });
                        }

                        if (from !== to) {
                            if (parent.popUp.state.isVisible) return;
                            // in case it is not visible determine the cursor position
                            const cursorPosition =
                                editor.view.coordsAtPos(from);
                            // update the position if cursorPosition is defined
                            cursorPosition &&
                                parent.popUp.setProps({
                                    getReferenceClientRect: () => ({
                                        width: 0,
                                        height: 0,
                                        top: cursorPosition.top + 20,
                                        left: cursorPosition.left,
                                        bottom: cursorPosition.bottom,
                                        right: cursorPosition.right,
                                    }),
                                });
                            // display the popup when mouse click is released
                            // multi-line selection is with this enabled
                            if (!parent.isClickListenerSet) {
                                element.addEventListener(
                                    'click',
                                    onClickHandler,
                                    { once: true },
                                );
                                parent.setIsClickListenerSet(true);
                            }
                        } else {
                            if (!parent.popUp.state.isVisible) return;
                            parent.popUp.hide();
                        }
                    },
                    onBlur() {
                        // clear the listener if the user clicks outside of the editor and the click listener is set
                        if (parent.isClickListenerSet) {
                            element.removeEventListener(
                                'click',
                                onClickHandler,
                            );
                            parent.setIsClickListenerSet(false);
                        }
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
        };
    };
}
