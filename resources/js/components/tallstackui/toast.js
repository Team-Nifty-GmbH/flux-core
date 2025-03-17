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

        Object.keys(event.detail).forEach((key) => {
            toast[key] = event.detail[key];
        });
    }
}

export default function (id) {
    return new TallstackToast(id);
}
