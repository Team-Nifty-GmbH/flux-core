import { computePosition, flip, shift, offset } from '@floating-ui/dom';

// Track currently open dropdown globally
let currentOpenDropdown = null;

export default function (parentElement, dropdownElement) {
    let sideEffect = null;

    return {
        popUp: null,
        floatingElement: null,
        isVisible: false,
        sideEffect() {
            if (this.popUp !== null && this.isVisible) {
                this.hide();
            }
        },
        hide() {
            if (this.floatingElement) {
                this.floatingElement.style.display = 'none';
                this.isVisible = false;

                if (currentOpenDropdown === this) {
                    currentOpenDropdown = null;
                }

                if (this.floatingElement._clickOutsideHandler) {
                    document.removeEventListener(
                        'click',
                        this.floatingElement._clickOutsideHandler,
                    );
                }
            }
        },
        show() {
            if (currentOpenDropdown && currentOpenDropdown !== this) {
                currentOpenDropdown.hide();
            }

            if (this.floatingElement) {
                this.floatingElement.style.display = 'block';
                this.isVisible = true;
                currentOpenDropdown = this;
                this.updatePosition();
            }
        },
        async updatePosition() {
            if (!this.floatingElement || !parentElement) return;

            const { x, y } = await computePosition(
                parentElement,
                this.floatingElement,
                {
                    placement: 'bottom',
                    middleware: [offset(8), flip(), shift({ padding: 5 })],
                },
            );

            Object.assign(this.floatingElement.style, {
                left: `${x}px`,
                top: `${y}px`,
            });
        },
        onClick() {
            if (this.popUp === null) {
                if (
                    dropdownElement !== undefined &&
                    parentElement !== undefined
                ) {
                    const actions = dropdownElement.content.cloneNode(true);

                    this.floatingElement = document.createElement('div');
                    this.floatingElement.className =
                        'floating-dropdown absolute z-50 bg-white dark:bg-secondary-800 rounded-md shadow-lg border border-secondary-200 dark:border-secondary-700 p-2';
                    this.floatingElement.style.display = 'none';
                    this.floatingElement.appendChild(actions);
                    parentElement.parentElement.appendChild(
                        this.floatingElement,
                    );

                    Alpine.initTree(this.floatingElement);

                    sideEffect = this.sideEffect.bind(this);
                    this.floatingElement.addEventListener('click', sideEffect);

                    const clickOutsideHandler = (event) => {
                        if (
                            !this.floatingElement.contains(event.target) &&
                            !parentElement.contains(event.target)
                        ) {
                            this.hide();
                        }
                    };
                    document.addEventListener('click', clickOutsideHandler);
                    this.floatingElement._clickOutsideHandler =
                        clickOutsideHandler;

                    this.popUp = true;
                    this.show();

                    return;
                }
            }

            if (!this.isVisible) {
                this.show();
            }
        },
    };
}
