import type { App } from 'vue';

import dayjs from 'dayjs';
import numbro from 'numbro';

/**
 * Format value to date or datetime string
 * @param {string} value
 * @param {boolean} datetime
 * @returns {undefined|string}
 */
function formatDate(value: string, datetime: boolean = false): string|undefined {
    if (!value) {
        return undefined;
    }

    return dayjs(value).format(datetime ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD');
}

/**
 * Converts a number of bytes to a human-readable string (metric)
 * @param {number} bytes The number of bytes to convert.
 * @param {number} [decimals=2] The number of decimal places to include in the result.
 * @returns {string} The number of bytes, formatted as a human-readable string (e.g. "1.23 MB").
 */
function formatBytes(bytes: number, decimals: number = 2): string {
    if (!bytes) {
        return '0';
    }

    return numbro(bytes).format({
        output: 'byte',
        base: 'binary',
        spaceSeparated: true,
        mantissa: decimals || 2
    });
}

/**
 * Converts a number of kbytes to a human-readable string (metric)
 * @param {number} kbytes The number of kbytes to convert.
 * @param {number} [decimals=2] The number of decimal places to include in the result.
 * @returns {string} The number of kbytes, formatted as a human-readable string (e.g. "1.23 MB").
 */
function formatKBytes(kbytes: number, decimals: number = 2): string {
    return formatBytes(kbytes * 1000, decimals);
}

const filters = {
    formatDate,
    formatBytes,
    formatKBytes
};

export default {
    install(app: App) {
        app.config.globalProperties.$filter = filters;
    },

    ...filters
}
