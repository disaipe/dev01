export function resizeArray(array, newSize, fill = undefined) {
    const changeSize = newSize - array.length;
    if (changeSize > 0) {
        return array.concat(Array(changeSize).fill(fill));
    }
    return array.slice(0, newSize);
}

export function makeMatrix(rows, cols, defaults = undefined) {
    return Array.from(Array(rows), () => new Array(cols).fill(undefined));
}
