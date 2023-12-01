import { h, resolveComponent } from 'vue';
import filters from '../../plugin/filters';

export function rawRenderer(value) {
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
            disabled: true
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
    datetime: datetimeRenderer,
    date: dateRenderer,
    select: selectRenderer,
    password: passwordRenderer
}
