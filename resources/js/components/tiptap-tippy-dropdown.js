export default function (parentElement, dropdownElement) {
    let sideEffect = null;
    return {
        popUp: null,
        sideEffect() {
            if (this.popUp !== null && this.popUp.state.isVisible) {
                this.popUp.hide();
            }
        },
        onClick() {
            if (this.popUp === null) {
                if (
                    dropdownElement !== undefined &&
                    parentElement !== undefined
                ) {
                    const actions = dropdownElement.content.cloneNode(true);
                    this.popUp = window.tippy(parentElement, {
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

            if (!this.popUp.state.isVisible) {
                this.popUp.show();
            }
        },
    };
}
