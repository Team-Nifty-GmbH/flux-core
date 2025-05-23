import TextStyle from '@tiptap/extension-text-style';

export const FontSizeConfig = TextStyle.extend({
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
