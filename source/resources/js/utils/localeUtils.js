import numbro from 'numbro';
import languages from 'numbro/dist/languages.min';

for (const language of Object.values(languages)) {
    numbro.registerLanguage(language);
}

export function getCountryCodes() {
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

export function setNumberFormatLanguage(language) {
    numbro.setLanguage(language, 'en-US');
}

export function getNumberSeparators() {
    const langData = numbro.languageData();

    return {
        decimal: langData.delimiters.decimal,
        thousand: langData.delimiters.thousands
    };
}

export function toFixed(value, mantissa = 2) {
    return numbro(value).format({
        thousandSeparated: false,
        mantissa
    });
}
