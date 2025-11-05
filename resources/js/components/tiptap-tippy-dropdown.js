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
