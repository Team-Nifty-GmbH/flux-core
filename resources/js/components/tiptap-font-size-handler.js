import TextStyle from "@tiptap/extension-text-style";


export const FontSizeConfig = TextStyle.extend({
    addOptions() {
        return {
            types: ['textStyle'],
        }
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
                                return {}
                            }

                            return {
                                style: `font-size: ${attributes.fontSize}px`,
                            }
                        },
                    },
                },
            },
        ]
    },
    addCommands() {
        return {
            setFontSize: fontSize => ({ chain }) => {
                return chain().setMark('textStyle', { fontSize });
            }
        }
    }
});


export default function (parentElement, dropdownElement) {
    return {
        popUpFontSize:null,
        onClick() {
            if(this.popUpFontSize === null) {
                if(dropdownElement !== undefined && parentElement !== undefined) {
                    const actions =  dropdownElement.content.cloneNode(true);
                    this.popUpFontSize = window.tippy(parentElement, {
                        content:  actions ?? 'not defined',
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom',
                    });
                }
                return;
            }

            if(this.popUpFontSize.state.isVisible) {
                this.popUpFontSize.hide();
            } else {
                this.popUpFontSize.show();
            }
        }
    }
}
