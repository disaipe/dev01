export function pxToPt(value) {
    return Math.round(3/4 * value)
}

export function ptToPx(value) {
    return Math.round(value / (3/4));
}

export function ptToEm(value) {
    return Math.round(value/12);
}
