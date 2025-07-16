export const STEP = 0.1; // cm

export function roundToTwoDecimal(value) {
    // Round to 0.01 cm
    return Math.round(value * 100) / 100;
}

export function roundToOneDecimal(value) {
    // Round to 0.1 cm
    return Math.round(value * 10) / 10;
}

export function moveHorizontal(element, delta) {}

export function moveVertical(element, delta) {}

export function moveDiagonal(element, deltaX, deltaY) {}

export function requestNextAnimationFrame() {}
