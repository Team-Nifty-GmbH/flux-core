class TallstackToast {
    constructor(id) {
        this.id = id;
        const el = this.getToastComponent();
        this.alpineComponent = el ? Alpine.$data(el) : null;
    }

    getToastByToastId(toastId) {
        return this.alpineComponent.toasts.find(
            (toast) => toast.toastId === toastId,
        );
    }

    getToastComponent() {
        const base = document.getElementById(this.id);
        return base.querySelector('[x-data^="tallstackui_toastBase"]');
    }

    upsertToast(event) {
        const toast = this.getToastByToastId(event.detail.toastId);

        if (!toast) {
            this.alpineComponent.add(event);

            return;
        }

        const wasPersistent = !!toast.persistent;

        Object.keys(event.detail).forEach((key) => {
            if (key === 'id' || key === 'toastId') {
                return;
            }
            toast[key] = event.detail[key];
        });

        // TallStackUI only arms its auto-dismiss timer once, on init. A toast that
        // was created persistent (e.g. a job progress toast) never gets a timer,
        // so when an update turns it into a timed toast we have to remove it ourselves.
        if (wasPersistent && !toast.persistent && toast.timeout && !toast._autoDismissArmed) {
            toast._autoDismissArmed = true;

            setTimeout(
                () => this.alpineComponent.remove(toast),
                toast.timeout * 1000,
            );
        }
    }
}

export default function (id) {
    return new TallstackToast(id);
}
