import Image from '@tiptap/extension-image';

export const ResizableImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: (element) => {
                    const raw =
                        element.getAttribute('width') ||
                        element.style.width?.replace('px', '') ||
                        null;
                    if (raw == null) return null;
                    const value = parseInt(raw, 10);
                    return Number.isNaN(value) ? null : value;
                },
                renderHTML: (attributes) => {
                    if (!attributes.width) return {};
                    return { width: attributes.width };
                },
            },
            height: {
                default: null,
                parseHTML: (element) => {
                    const raw =
                        element.getAttribute('height') ||
                        element.style.height?.replace('px', '') ||
                        null;
                    if (raw == null) return null;
                    const value = parseInt(raw, 10);
                    return Number.isNaN(value) ? null : value;
                },
                renderHTML: (attributes) => {
                    if (!attributes.height) return {};
                    return { height: attributes.height };
                },
            },
        };
    },

    addNodeView() {
        return ({ node, editor, getPos }) => {
            const wrapper = document.createElement('div');
            Object.assign(wrapper.style, {
                display: 'inline-block',
                position: 'relative',
                lineHeight: '0',
                maxWidth: '100%',
            });

            const img = document.createElement('img');
            img.src = node.attrs.src;
            if (node.attrs.alt) img.alt = node.attrs.alt;
            if (node.attrs.title) img.title = node.attrs.title;
            if (node.attrs.width) img.style.width = `${node.attrs.width}px`;
            if (node.attrs.height) img.style.height = `${node.attrs.height}px`;
            img.style.maxWidth = '100%';
            img.style.display = 'block';

            wrapper.appendChild(img);

            if (editor.isEditable) {
                const handle = document.createElement('div');
                Object.assign(handle.style, {
                    position: 'absolute',
                    top: '0',
                    right: '0',
                    width: '16px',
                    height: '16px',
                    cursor: 'nesw-resize',
                    opacity: '0',
                    transition: 'opacity 150ms',
                    backgroundImage:
                        'linear-gradient(45deg, transparent 50%, rgb(59, 130, 246) 50%, rgb(59, 130, 246) 55%, transparent 55%, transparent 65%, rgb(59, 130, 246) 65%, rgb(59, 130, 246) 70%, transparent 70%, transparent 80%, rgb(59, 130, 246) 80%, rgb(59, 130, 246) 85%, transparent 85%)',
                });

                wrapper.addEventListener('mouseenter', () => {
                    if (editor.isEditable) {
                        handle.style.opacity = '1';
                        img.style.outline = '2px solid rgb(59, 130, 246)';
                        img.style.outlineOffset = '-2px';
                    }
                });
                wrapper.addEventListener('mouseleave', () => {
                    handle.style.opacity = '0';
                    img.style.outline = '';
                    img.style.outlineOffset = '';
                });

                wrapper.appendChild(handle);

                let startX, startWidth, aspectRatio;

                handle.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    startX = e.clientX;
                    startWidth = img.offsetWidth || img.naturalWidth || 1;
                    const startHeight =
                        img.offsetHeight || img.naturalHeight || 1;
                    aspectRatio = startHeight / startWidth;

                    const onMouseMove = (moveEvent) => {
                        const dx = moveEvent.clientX - startX;
                        const newWidth = Math.max(50, startWidth + dx);
                        const newHeight = Math.round(newWidth * aspectRatio);
                        img.style.width = `${newWidth}px`;
                        img.style.height = `${newHeight}px`;
                    };

                    const onMouseUp = () => {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);

                        if (typeof getPos === 'function') {
                            const pos = getPos();
                            if (pos != null) {
                                const currentNode =
                                    editor.state.doc.nodeAt(pos);
                                if (currentNode) {
                                    editor.view.dispatch(
                                        editor.state.tr.setNodeMarkup(
                                            pos,
                                            undefined,
                                            {
                                                ...currentNode.attrs,
                                                width: img.offsetWidth,
                                                height: img.offsetHeight,
                                            },
                                        ),
                                    );
                                }
                            }
                        }
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            }

            return {
                dom: wrapper,
                update: (updatedNode) => {
                    if (updatedNode.type.name !== 'image') return false;
                    img.src = updatedNode.attrs.src;
                    if (updatedNode.attrs.alt) img.alt = updatedNode.attrs.alt;
                    if (updatedNode.attrs.title)
                        img.title = updatedNode.attrs.title;
                    img.style.width = updatedNode.attrs.width
                        ? `${updatedNode.attrs.width}px`
                        : '';
                    img.style.height = updatedNode.attrs.height
                        ? `${updatedNode.attrs.height}px`
                        : '';
                    return true;
                },
            };
        };
    },
});
