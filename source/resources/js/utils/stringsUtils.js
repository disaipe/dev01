export function fromPascalCase(str) {
    return str
        .match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g)
        .join(' ');
}

export function snake(str) {
    return str
        .match(/[A-Z](?=[A-Z][a-z]+|\b)|[A-Z]?[a-z]+|[A-Z]|[0-9]+/g)
        .map((x) => x.toLowerCase())
        .join('_');
}

export function upperFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export function lowerFirstLetter(string) {
    return string.charAt(0).toLowerCase() + string.slice(1);
}
