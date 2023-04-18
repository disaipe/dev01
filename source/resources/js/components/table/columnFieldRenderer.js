import { h, resolveComponent } from 'vue';
import filters from '../../plugin/filters';

export function rawRenderer(value) {
    return () => value;
}

export function relationRenderer(value) {
    return () => h('span', {}, value?.$getName());
}

export function switchRenderer(value, row, field) {
    return () => h(
        resolveComponent('el-switch'),
        {
            modelValue: row[field],
            size: 'small',
            disabled: true
        }
    );
}

export function datetimeRenderer(value) {
    return () => h('span', filters.formatDate(value, true));
}

export function dateRenderer(value) {
    return () => h('span', filters.formatDate(value));
}

export function selectRenderer(value, row, field, fields) {
    return () => h('span', {}, fields[field].options[value]);
}

export function passwordRenderer() {
    return () => '******';
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
