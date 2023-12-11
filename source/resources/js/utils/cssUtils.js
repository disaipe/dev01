export function parseSize(value) {
    if (value) {
        const groups = value.match(/(?<value>[\d.]+)(?<unit>\w+)/).groups;

        if (groups && groups.value) {
            groups.value = parseFloat(groups.value);
        }

        return groups;
    }

    return {
        value: null,
        unit: null
    };
}

export function pxToPt(value) {
    return Math.round(3/4 * value)
}

export function ptToPx(value) {
    return Math.round(value / (3/4));
}

export function ptToEm(value) {
    return Math.round(value/12);
}
