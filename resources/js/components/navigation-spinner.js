export default function() {
    let spinnerTimeout;
    let spinnerVisible = false;

    function showSpinner() {
        document.body.style.pointerEvents = 'none';
        document.body.style.cursor = 'wait';

        const overlay = document.getElementById('loading-overlay');
        const spinner = document.getElementById('loading-overlay-spinner');

        if (!overlay || !spinner) return;

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
