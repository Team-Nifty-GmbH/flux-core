import { TextStyle } from '@tiptap/extension-text-style';

export const FontSizeColorConfig = TextStyle.extend({
    addOptions() {
        return {
            types: ['textStyle'],
        };
    },
    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    fontSize: {
                        default: null,
                        parseHTML: (element) => element.style.fontSize,
                        renderHTML: (attributes) => {
                            if (!attributes.fontSize) {
                                return {};
                            }

                            if (typeof attributes.fontSize === 'number') {
                                return {
                                    style: `font-size: ${attributes.fontSize}px`,
                                };
                            }

                            if (
                                typeof attributes.fontSize === 'string' &&
                                attributes.fontSize.includes('px')
                            ) {
                                return {
                                    style: `font-size: ${attributes.fontSize}`,
                                };
                            }

                            return {};
                        },
                    },
                    color: {
                        default: null,
                        parseHTML: (element) => element.style.color,
                        renderHTML: (attributes) => {
                            if (!attributes.color) {
                                return {};
                            }
                            return {
                                style: `color: ${attributes.color}`,
                            };
                        },
                    },
                    backgroundColor: {
                        default: null,
                        parseHTML: (element) => element.style.backgroundColor,
                        renderHTML: (attributes) => {
                            if (!attributes.backgroundColor) {
                                return {};
                            }
                            return {
                                style: `background-color: ${attributes.backgroundColor}`,
                            };
                        },
                    },
                },
            },
        ];
    },
    addCommands() {
        return {
            setFontSize:
                (fontSize) =>
                ({ chain }) => {
                    return chain().setMark('textStyle', { fontSize });
                },
            setColor:
                (color) =>
                ({ chain }) => {
                    return chain().setMark('textStyle', { color }).run();
                },
            unsetColor:
                () =>
                ({ chain }) => {
                    return chain().setMark('textStyle', { color: null }).run();
                },
            setBackgroundColor:
                (backgroundColor) =>
                ({ chain }) => {
                    return chain()
                        .setMark('textStyle', { backgroundColor })
                        .run();
                },
            unsetBackgroundColor:
                () =>
                ({ chain }) => {
                    return chain()
                        .setMark('textStyle', { backgroundColor: null })
                        .run();
                },
        };
    },
});

export default function (parentElement, dropdownElement) {
    let sideEffect = null;
    return {
        popUpFontSize: null,
        sideEffect() {
            if (
                this.popUpFontSize !== null &&
                this.popUpFontSize.state.isVisible
            ) {
                this.popUpFontSize.hide();
            }
        },
        onClick() {
            if (this.popUpFontSize === null) {
                if (
                    dropdownElement !== undefined &&
                    parentElement !== undefined
                ) {
                    const actions = dropdownElement.content.cloneNode(true);
                    this.popUpFontSize = window.tippy(parentElement, {
                        content: actions ?? 'not defined',
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom',
                        onShow: (instance) => {
                            sideEffect = this.sideEffect.bind(this);
                            instance.popper.addEventListener(
                                'click',
                                sideEffect,
                            );
                        },
                        onHide: (instance) =>
                            sideEffect &&
                            instance.popper.removeEventListener(
                                'click',
                                sideEffect,
                            ),
                    });

                    return;
                }
            }

            if (!this.popUpFontSize.state.isVisible) {
                this.popUpFontSize.show();
            }
        },
    };
}
