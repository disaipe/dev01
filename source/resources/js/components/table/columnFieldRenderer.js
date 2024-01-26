import { h, resolveComponent } from 'vue';
import filters from '../../plugin/filters';

export function rawRenderer(value, row, field, fields) {
    const filter = fields[field]?.filter;

    if (Array.isArray(filter)) {
        const [filterMethod, ...args] = filter;

        if (typeof filters[filterMethod] === 'function') {
            return filters[filterMethod].call(null, value, ...args);
        }
    }

    return value;
}

export function relationRenderer(value) {
    if (value) {
        const values = Array.isArray(value) ? value : [value];

        return values.map((value) => h(
            'div',
            {},
            value?.$getName?.()
        ));
    }

    return undefined;
}

export function switchRenderer(value) {
    return h(
        resolveComponent('el-switch'),
        {
            modelValue: value,
            size: 'small',
            readonly: true
        }
    );
}

export function checkboxRenderer(value) {
    return h(
        resolveComponent('el-checkbox'),
        {
            modelValue: value,
            readonly: true
        }
    );
}

export function datetimeRenderer(value) {
    return h('span', filters.formatDate(value, true));
}

export function dateRenderer(value) {
    return h('span', filters.formatDate(value));
}

export function selectRenderer(value, row, field, fields) {
    return h('span', {}, fields[field].options[value]);
}

export function passwordRenderer() {
    return '******';
}

export default {
    raw: rawRenderer,
    relation: relationRenderer,
    switch: switchRenderer,
    checkbox: checkboxRenderer,
    datetime: datetimeRenderer,
    date: dateRenderer,
    select: selectRenderer,
    password: passwordRenderer
}
