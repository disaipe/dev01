import dayjs from 'dayjs';

const filters = {};

/**
 * Format value to date or datetime string
 * @param {string} value
 * @param {boolean} datetime
 * @returns {undefined|string}
 */
filters.formatDate = (value, datetime = false) => {
    if (!value) {
        return undefined;
    }

    return dayjs(value).format(datetime ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD');
};

export default {
    install(app) {
        app.config.globalProperties.$filter = filters;
    }
}
