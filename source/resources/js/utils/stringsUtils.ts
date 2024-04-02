export function fromPascalCase(str: string): string {
    const matches = str.match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g);

    return matches
        ? matches.join(' ')
        : str;
}

export function snake(str: string): string {
    const matches = str.match(/[A-Z](?=[A-Z][a-z]+|\b)|[A-Z]?[a-z]+|[A-Z]|[0-9]+/g);

    return matches
        ? matches.map((x) => x.toLowerCase()).join('_')
        : str;
}

export function upperFirstLetter(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function lowerFirstLetter(str: string): string {
    return str.charAt(0).toLowerCase() + str.slice(1);
}
