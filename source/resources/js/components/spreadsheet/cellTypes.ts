import type { Ref } from 'vue'
import type { Model } from 'pinia-orm';
import type { SpreadSheetCellType } from '@/types';

import { SelectEditor } from 'handsontable/editors';

import {
    serviceNameCellRenderer,
    serviceCountCellRenderer,
    servicePriceCellRenderer
} from './cellRenderers';

export type CellTypesContext = {
    services: Ref<Model[]>
}

const getServiceSelectOption = (services: Ref<Model[]>, type: string) => {
    if (!services.value) {
        return [];
    }

    return services.value.reduce((acc: Record<string, string>, cur: Model) => {
            acc[`SERVICE#${cur.$getKey()}#${type}`] = cur.$getName();
            return acc;
        }, {});
};

export default ({ services }: CellTypesContext): Record<string, SpreadSheetCellType> => ({
    serviceName: {
        pattern: /SERVICE#.+?#NAME/,
        renderer: serviceNameCellRenderer,
        meta: {
            editor: SelectEditor,
            selectOptions: () => getServiceSelectOption(services, 'NAME'),
            type: 'text',
            className: 'cell-service-name'
        }
    },
    serviceCount: {
        pattern: /SERVICE#.+?#COUNT/,
        renderer: serviceCountCellRenderer,
        meta: {
            editor: SelectEditor,
            selectOptions: () => getServiceSelectOption(services, 'COUNT'),
            type: 'numeric',
            className: 'cell-service-count'
        }
    },
    servicePrice: {
        pattern: /SERVICE#.+?#PRICE/,
        renderer: servicePriceCellRenderer,
        meta: {
            editor: SelectEditor,
            selectOptions: () => getServiceSelectOption(services, 'PRICE'),
            type: 'numeric',
            className: 'cell-service-price'
        }
    }
});
