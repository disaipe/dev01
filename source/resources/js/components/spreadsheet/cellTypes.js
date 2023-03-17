export function isServiceNameCell(cellValue) {
    return typeof(cellValue) === 'string' && /SERVICE#.+?#NAME/.test(cellValue);
}

export function isServiceCountCell(cellValue) {
    return typeof(cellValue) === 'string' && /SERVICE#.+?#COUNT/.test(cellValue);
}

export function isServicePriceCell(cellValue) {
    return typeof(cellValue) === 'string' && /SERVICE#.+?#PRICE/.test(cellValue);
}
