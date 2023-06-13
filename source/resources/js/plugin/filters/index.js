import dayjs from 'dayjs';

/**
 * Format value to date or datetime string
 * @param {string} value
 * @param {boolean} datetime
 * @returns {undefined|string}
 */
function formatDate(value, datetime = false) {
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
function formatBytes(bytes, decimals= 2) {
    if (!bytes) {
        return '0 Bytes';
    }

    const k = 1000;
    const dm = decimals || 2;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Converts a number of kbytes to a human-readable string (metric)
 * @param {number} kbytes The number of kbytes to convert.
 * @param {number} [decimals=2] The number of decimal places to include in the result.
 * @returns {string} The number of kbytes, formatted as a human-readable string (e.g. "1.23 MB").
 */
function formatKBytes(kbytes, decimals = 2) {
    return formatBytes(kbytes * 1000, decimals);
}

/**
 * Converts a number of bytes to a human-readable string.
 * @param {number} bytes The number of bytes to convert.
 * @param {number} [decimals=2] The number of decimal places to include in the result.
 * @returns {string} The number of bytes, formatted as a human-readable string (e.g. "1.23 MB").
 */
function formatBytes2(bytes, decimals= 2) {
    if (!bytes) {
        return '0 Bytes';
    }

    const k = 1024;
    const dm = decimals || 2;
    const sizes = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Converts a number of kbytes to a human-readable string.
 * @param {number} kbytes The number of kbytes to convert.
 * @param {number} [decimals=2] The number of decimal places to include in the result.
 * @returns {string} The number of kbytes, formatted as a human-readable string (e.g. "1.23 MB").
 */
function formatKBytes2(kbytes, decimals = 2) {
    return formatBytes(kbytes * 1024, decimals);
}

const filters = {
    formatDate,
    formatBytes,
    formatKBytes,
    formatBytes2,
    formatKBytes2
};

export default {
    install(app) {
        app.config.globalProperties.$filter = filters;
    },

    ...filters
}
