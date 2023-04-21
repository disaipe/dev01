import { h, resolveComponent } from 'vue';
import filters from '../../plugin/filters';

export function rawRenderer(valueRef) {
    return () => valueRef.value;
}

export function relationRenderer(valueRef) {
    return () => h('span', {}, valueRef.value?.$getName?.());
}

export function switchRenderer(valueRef) {
    return () => h(
        resolveComponent('el-switch'),
        {
            modelValue: valueRef.value,
            size: 'small',
            disabled: true
        }
    );
}

export function datetimeRenderer(valueRef) {
    return () => h('span', filters.formatDate(valueRef.value, true));
}

export function dateRenderer(valueRef) {
    return () => h('span', filters.formatDate(valueRef.value));
}

export function selectRenderer(valueRef, row, field, fields) {
    return () => h('span', {}, fields[field].options[valueRef.value]);
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
