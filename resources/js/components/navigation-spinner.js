export default function () {
    let spinnerTimeout;
    let spinnerVisible = false;

    function showSpinner(event) {
        if (
            event.type === 'beforeunload' &&
            event.target.location.href === window.location.href
        ) {
            return;
        }

        document.body.style.pointerEvents = 'none';
        document.body.style.cursor = 'wait';

        const overlay = document.getElementById('loading-overlay');
        const spinner = document.getElementById('loading-overlay-spinner');

        if (!overlay || !spinner) return;

        const main = document.querySelector('main');
        const box = main?.getBoundingClientRect();
        const fillsViewport =
            !box || (box.left <= 0 && box.right >= window.innerWidth);

        for (const side of ['top', 'left', 'right', 'bottom']) {
            spinner.style[side] = '';
        }

        if (!fillsViewport) {
            spinner.style.top = `${Math.round(box.top)}px`;
            spinner.style.left = `${Math.round(box.left)}px`;
            spinner.style.right = `${Math.round(window.innerWidth - box.right)}px`;
            spinner.style.bottom = `${Math.round(window.innerHeight - box.bottom)}px`;
        }

        overlay.classList.remove('hidden');

        spinnerTimeout = setTimeout(() => {
            spinner.classList.remove('opacity-0');
            spinnerVisible = true;
        }, 400);
    }

    function hideSpinner() {
        document.body.style.pointerEvents = 'auto';
        document.body.style.cursor = 'default';

        const overlay = document.getElementById('loading-overlay');
        const spinner = document.getElementById('loading-overlay-spinner');

        if (!overlay || !spinner) return;

        clearTimeout(spinnerTimeout);

        if (spinnerVisible) {
            spinner.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
                spinnerVisible = false;
            }, 200);
        } else {
            overlay.classList.add('hidden');
        }
    }

    // Livewire navigation events
    document.addEventListener('livewire:navigate', showSpinner);
    document.addEventListener('livewire:navigated', hideSpinner);

    // Detect full page reloads or traditional navigation
    window.addEventListener('beforeunload', showSpinner);
}
