import numbro from 'numbro';
/** @ts-ignore */
import languages from 'numbro/dist/languages.min';

for (const language of Object.values(languages) as numbro.NumbroLanguage[]) {
    numbro.registerLanguage(language);
}

export function getCountryCodes(): Record<string, string> {
    return {
        'cs-CZ': 'Czech',
        'da-DK': 'Danish',
        'de-CH': 'German (Switzerland)',
        'de-DE': 'German (Standard)',
        'en-GB': 'English (United Kingdom)',
        'en-US': 'English (United States)',
        'en-ZA': 'English (South Africa)',
        'es': 'Spanish (Spain)',
        'es-AR': 'Spanish (Argentina)',
        'es-ES': 'Spanish (Spain)',
        'et-EE': 'Estonian (Estonia)',
        'fa-IR': 'Farsi (Iran)',
        'fi-FI': 'Finnish (Finland)',
        'fil-PH': 'Filipino',
        'fr-CA': 'French (Canada)',
        'fr-CH': 'French (Switzerland)',
        'fr-FR': 'French (France)',
        'hu-HU': 'Hungarian (Hungary)',
        'it-IT': 'Italian (Standard)',
        'ja-JP': 'Japanese',
        'lv-LV': 'Latvian (Latvia)',
        'nb-NO': 'Norwegian (Norway)',
        'nl-BE': 'Dutch (Belgium)',
        'nl-NL': 'Dutch (The Netherlands)',
        'pl-PL': 'Polish (Poland)',
        'pt-BR': 'Portuguese (Brazil)',
        'pt-PT': 'Portuguese (Portugal)',
        'ru-RU': 'Russian (Russia)',
        'ru-UA': 'Russian (Ukraine)',
        'sk-SK': 'Slovak',
        'sv-SE': 'Swedish',
        'th-TH': 'Thai',
        'tr-TR': 'Turkish',
        'uk-UA': 'Ukrainian',
        'zh-CN': 'Chinese (PRC)'
    };
}

export function setNumberFormatLanguage(language: string) {
    numbro.setLanguage(language, 'en-US');
}

export function getNumberSeparators(locale?: string): { decimal: '.' | ',' | undefined, thousand: string } {
    const langData = numbro.languageData(locale);
    const decimal = langData.delimiters.decimal;

    return {
        decimal: ['.', ','].includes(decimal) ? decimal as ','|'.' : undefined,
        thousand: langData.delimiters.thousands
    };
}

export function toFixed(value: number, mantissa: number = 2): string {
    return numbro(value).format({
        thousandSeparated: false,
        mantissa
    });
}
