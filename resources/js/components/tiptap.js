import { Editor } from '@tiptap/core';
import { LiteralTab } from './tiptap-literal-tab-handler.js';
import { FontSizeLineHeightColorConfig } from './tiptap-font-size-line-height-color-handler.js';
import { TextAlignConfig } from './tiptap-text-align-handler.js';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import { MentionConfig } from './tiptap-mention-handler.js';
import { BladeVariableConfig } from './tiptap-blade-variable.js';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';
import { Table } from './tiptap-table.js';

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
            floatingElement: null,
            isFloatingVisible: false,
            isClickListenerSet: false,
            editorState: 0,
            setIsClickListenerSet(value) {
                this.isClickListenerSet = value;
            },
            async updateFloatingPosition(referenceElement) {
                if (!this.floatingElement || !referenceElement) return;

                const { x, y } = await computePosition(
                    referenceElement,
                    this.floatingElement,
                    {
                        placement: 'left',
                        middleware: [offset(8), flip(), shift({ padding: 5 })],
                    },
                );

                Object.assign(this.floatingElement.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                });
            },
            initTextArea(
                id,
                element,
                isTransparent,
                showTooltipDropdown,
                fullHeight,
                showEditorPadding,
                defaultFontSize,
            ) {
                const popUp = this.$refs[`popWindow-${id}`];
                const controlPanel = this.$refs[`controlPanel-${id}`];
                const commands = this.$refs[`commands-${id}`];
                let actions = null;

                if (showTooltipDropdown && popUp !== null) {
                    const popUpNode = popUp.content.cloneNode(true);
                    const commandsNode = commands.content.cloneNode(true);
                    popUpNode.appendChild(commandsNode);
                    actions = popUpNode;
                } else {
                    controlPanel.appendChild(commands.content.cloneNode(true));
                }

                const parent = this;
                let virtualReference = null;

                const onClickHandler = function () {
                    if (parent.floatingElement) {
                        parent.floatingElement.style.display = 'block';
                        parent.isFloatingVisible = true;
                        parent.updateFloatingPosition(virtualReference);
                    }

                    parent.setIsClickListenerSet(false);
                };

                _editor = new Editor({
                    element: element,
                    extensions: [
                        StarterKit.configure({
                            link: false,
                        }),
                        Link.configure({
                            openOnClick: false,
                            HTMLAttributes: {
                                class: 'text-primary-600 dark:text-primary-400 underline hover:text-primary-700 dark:hover:text-primary-300',
                            },
                        }),
                        FontSizeLineHeightColorConfig,
                        LiteralTab,
                        TextAlignConfig,
                        Table,
                        MentionConfig(searchModel, element),
                        BladeVariableConfig(),
                    ],
                    timeout: null,
                    content: this.content,
                    editable: this.editable,
                    editorProps: {
                        attributes: {
                            class: `${isTransparent ? 'bg-transparent' : 'dark:bg-secondary-800'} ${showTooltipDropdown ? 'rounded-md' : 'rounded-b-md'} \
                                prose prose-sm dark:prose-invert max-w-full content-editable-placeholder dark:text-gray-50 placeholder-secondary-400 dark:placeholder-secondary-500 \
                                border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block \
                                 ${fullHeight ? 'h-full' : 'min-h-[85px]'} w-full border p-3 ${showEditorPadding ? 'p-3' : 'no-margin'} shadow-sm transition duration-100 ease-in-out focus:outline-none sm:text-sm`,
                            style: `${defaultFontSize !== null ? `font-size:${defaultFontSize}px;` : ''}`,
                        },
                    },
                    onSelectionUpdate: ({ editor }) => {
                        parent.editorState = Date.now();

                        if (!showTooltipDropdown) {
                            return;
                        }

                        const { from, to } = editor.state.selection;

                        if (parent.floatingElement === null) {
                            parent.floatingElement =
                                document.createElement('div');
                            parent.floatingElement.className =
                                'floating-dropdown absolute flex items-center flex-wrap w-fit max-w-[300px] z-50 bg-white dark:bg-secondary-800 rounded-md shadow-lg border border-secondary-200 dark:border-secondary-700 p-2';
                            parent.floatingElement.style.display = 'none';
                            parent.floatingElement.setAttribute(
                                'x-show',
                                'isFloatingVisible',
                            );
                            parent.floatingElement.setAttribute(
                                'x-on:click.outside',
                                'isFloatingVisible = false; floatingElement.style.display = "none"',
                            );
                            parent.floatingElement.appendChild(actions);
                            element.parentElement.appendChild(
                                parent.floatingElement,
                            );
                            Alpine.initTree(parent.floatingElement);
                        }

                        if (from !== to) {
                            if (parent.isFloatingVisible) return;

                            const cursorPosition =
                                editor.view.coordsAtPos(from);

                            virtualReference = {
                                getBoundingClientRect: () => ({
                                    width: 0,
                                    height: 0,
                                    top: cursorPosition.top,
                                    left: cursorPosition.left + 20,
                                    bottom: cursorPosition.bottom,
                                    right: cursorPosition.right,
                                    x: cursorPosition.left + 20,
                                    y: cursorPosition.top,
                                }),
                            };

                            if (!parent.isClickListenerSet) {
                                element.addEventListener(
                                    'click',
                                    onClickHandler,
                                    { once: true },
                                );
                                parent.setIsClickListenerSet(true);
                            }
                        } else {
                            if (!parent.isFloatingVisible) return;
                            parent.floatingElement.style.display = 'none';
                            parent.isFloatingVisible = false;
                        }
                    },
                    onBlur() {
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
                    onTransaction: ({ editor }) => {
                        this.editorState = Date.now();
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
