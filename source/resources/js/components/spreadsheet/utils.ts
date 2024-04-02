import { toFixed } from '../../utils/localeUtils';

export function isFormula(value: any) {
    return value
        && typeof(value) === 'string'
        && value.startsWith('=');
}

export function applyBindings(value: any, replacements: Record<string, any>) {
    let newValue = value;

    for (const [key, replacement] of Object.entries(replacements)) {
        const keyWithDelimiters = key.replaceAll('_', '[_#]');

        const pattern1 = `(?:(?:^|\\s)(${keyWithDelimiters})(?:\\s|$))`;
        const pattern2 = `(?:{(${keyWithDelimiters})})`;
        const regex = new RegExp(`${pattern1}|${pattern2}`, 'g');

        if (regex.test(value)) {
            let formattedReplacement = replacement;

            if (typeof(replacement) === 'number') {
                formattedReplacement = toFixed(replacement);
            }

            newValue = newValue.replaceAll(regex, formattedReplacement);
        }
    }

    return newValue;
}
