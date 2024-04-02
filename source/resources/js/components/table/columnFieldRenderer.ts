import type { Model } from 'pinia-orm';
import { h, resolveComponent } from 'vue';

import type { ModelSchema } from '@/types';
import { filters } from '@/plugin/filters';

export function rawRenderer(value: any, row: any, field: string, fields: ModelSchema) {
    const filter = fields[field]?.filter;

    if (Array.isArray(filter)) {
        const [filterMethodName, ...args] = filter;

        const filterMethod = filters[filterMethodName];

        if (filterMethod) {
            return filterMethod(value, ...args);
        }
    }

    return value;
}

export function relationRenderer(value: Model) {
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

export function switchRenderer(value: string | number | boolean) {
    return h(
        resolveComponent('el-switch'),
        {
            modelValue: value,
            size: 'small',
            readonly: true
        }
    );
}

export function checkboxRenderer(value: string | number | boolean) {
    return h(
        resolveComponent('el-checkbox'),
        {
            modelValue: value,
            readonly: true
        }
    );
}

export function datetimeRenderer(value: string) {
    return h('span', filters.formatDate(value, true));
}

export function dateRenderer(value: string) {
    return h('span', filters.formatDate(value));
}

export function selectRenderer(value: any, row: any, field: string, fields: ModelSchema) {
    return h('span', {}, fields[field].options?.[value] || []);
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
