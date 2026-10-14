const RELOAD_DELAY = 2000;
const DAY = 24 * 60 * 60 * 1000;

window.fluxWidgetLiveUpdates = ($wire, { method, listeners, midnightIn }) => {
    const el = $wire.$el;
    el.__fluxLiveUpdates?.();

    let reloadTimeout = null;
    let midnightTimeout = null;

    const unsubscribe = () => {
        clearTimeout(reloadTimeout);
        clearTimeout(midnightTimeout);
        listeners.forEach(([channel, event]) =>
            window.Echo?.private(channel).stopListening(event, reload),
        );
        delete el.__fluxLiveUpdates;
    };

    const reload = () => {
        if (!el.isConnected) {
            unsubscribe();

            return;
        }

        if (reloadTimeout) {
            return;
        }

        reloadTimeout = setTimeout(() => {
            reloadTimeout = null;
            $wire.$call(method);
        }, RELOAD_DELAY);
    };

    const reloadAtMidnight = (delay) => {
        midnightTimeout = setTimeout(() => {
            if (!el.isConnected) {
                unsubscribe();

                return;
            }

            $wire.$call(method);
            reloadAtMidnight(DAY);
        }, delay);
    };

    listeners.forEach(([channel, event]) =>
        window.Echo?.private(channel).listen(event, reload),
    );

    if (midnightIn !== null) {
        reloadAtMidnight(midnightIn);
    }

    el.__fluxLiveUpdates = unsubscribe;
};
