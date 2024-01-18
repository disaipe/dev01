export function getNumberSeparators() {
    // default
    const res = {
        decimal: '.',
        thousand: ''
    };

    // convert a number formatted according to locale
    const str = parseFloat(1234.56).toLocaleString();

    // if the resulting number does not contain previous number
    // (i.e. in some Arabic formats), return defaults
    if (!str.match('1')) {
        return res;
    }

    // get decimal and a thousand separators
    res.decimal = str.replace(/.*4(.*)5.*/, "$1");
    res.thousand = str.replace(/.*1(.*)2.*/, "$1");

    return res;
}

export function toFixed(value) {
    return value.toLocaleString(undefined, {
        useGrouping: false,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
