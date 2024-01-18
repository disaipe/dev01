import {
    serviceNameCellRenderer,
    serviceCountCellRenderer,
    servicePriceCellRenderer
} from './cellRenderers';

import { SelectEditor } from 'handsontable/editors';

const getServiceSelectOption = (services, type) => {
    if (!services.value) {
        return [];
    }

    return services.value.reduce((acc, cur) => {
            acc[`SERVICE#${cur.$getKey()}#${type}`] = cur.$getName();
            return acc;
        }, {});
};

export default ({ services, contract }) => ({
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
