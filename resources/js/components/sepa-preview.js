import { entangle } from 'alpinejs/src/entangle.js';

export default function sepaPreview($wire, $refs) {
    return {
        tenant: $wire.entangle('tenant'),
        loading: false,
        async onInit() {
            // fetch route
            if ($refs) {
                this.$nextTick(async () => {
                    this.$watch(
                        () => this.tenant,
                        async (newValue) => {},
                    );
                });
            }
        },
        async fetchRoute() {},
    };
}
