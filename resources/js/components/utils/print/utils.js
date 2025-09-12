export const STEP = 0.1; // cm

export function roundToOneDecimal(value) {
    // Round to 0.1 cm
    return Math.round(value * 10) / 10;
}

export function roundToTwoDecimals(value) {
    // Round to 0.01 cm
    return Math.round(value * 100) / 100;
}

export function intersectionHandlerFactory($store) {
    return (entries) => {
        entries.forEach((entry) => {
            const { target, isIntersecting } = entry;
            if (isIntersecting) {
                // remove the element from array if are back in view
                const index = $store.elementsOutOfView.indexOf(target.id);
                if (index !== -1) {
                    $store.elementsOutOfView.splice(index, 1);
                }
            } else {
                $store.elementsOutOfView.push(target.id);
            }
        });
    };
}

// TODO: maybe to change to requestAnimationFrame?
export function nextTick() {
    return Promise.resolve();
}
