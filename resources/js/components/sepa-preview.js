export default function sepaPreview($wire, $refs) {
    return {
        route: null,
        tenant: $wire.entangle('tenant'),
        async onInit(initTenantId) {
            console.log(
                'Initializing SEPA Preview with tenant ID:',
                initTenantId,
            );
            this.route = await $wire.previewRoute(initTenantId);
            // fetch route
            if ($refs && this.route) {
                this.$nextTick(async () => {
                    // add route to iframe
                    $refs.frame.src = this.route;
                    this.$watch(
                        () => this.tenant,
                        this._tenantWatcher.bind(this),
                    );
                });
            }
        },
        async _tenantWatcher(newValue, oldValue) {
            if (newValue?.id !== oldValue?.id) {
                this.$nextTick(async () => {
                    // on tenant change, fetch new route and update iframe
                    this.route = await $wire.previewRoute(newValue?.id);
                    if (this.route && $refs.frame) {
                        $refs.frame.src = this.route;
                    }
                });
            }
        },
    };
}
