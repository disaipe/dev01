import type { Ref } from 'vue';
import type Handsontable from 'handsontable';
import cellTypes, { type CellTypesContext } from './cellTypes';

export function setCellFormat(instance: Ref<Handsontable>, row: number, col: number, type: string, context: CellTypesContext) {
    if (!instance.value) {
        return;
    }

    const typeDef = cellTypes(context)[type];

    if (typeDef) {
        const { data, meta } = typeDef;

        instance.value.setCellMetaObject(row, col, {
            renderer: typeDef.renderer,
            ...(meta || {})
        });

        if (data) {
            instance.value.setSourceDataAtCell(row, col, data);
        }

        instance.value.render();
    } else {
        console.warn(`Cell type format "${type} not registered`);
    }
}

export function cellFormatter(instance: Ref<Handsontable>, value: any, row: number, col: number, context: CellTypesContext) {
    if (!instance.value) {
        return;
    }

    const types = cellTypes(context);

    if (value && typeof(value) === 'string') {
        for (const [type, typeDef] of Object.entries(types)) {
            if (typeDef.pattern.test(value)) {
                setCellFormat(instance, row, col, type, context);
            }
        }
    }
}
