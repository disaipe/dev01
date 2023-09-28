import {
    serviceNameCellRenderer,
    serviceCountCellRenderer,
    servicePriceCellRenderer,
    contractNumberRenderer,
    contractDateRenderer,
    totalSumRenderer,
    totalVatRenderer,
    totalWithVatRenderer
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
    },

    contractNumber: {
        pattern: /CONTRACT#NUMBER/,
        renderer: contractNumberRenderer,
        data: 'CONTRACT#NUMBER',
        meta: {
            className: 'cell-contract-number'
        }
    },

    contractDate: {
        pattern: /CONTRACT#DATE/,
        renderer: contractDateRenderer,
        data: 'CONTRACT#DATE',
        meta: {
            className: 'cell-contract-date'
        }
    },

    totalSum: {
        pattern: /TOTAL/,
        renderer: totalSumRenderer,
        data: 'TOTAL',
        meta: {
            className: 'cell-total'
        }
    },

    totalVat: {
        pattern: /TOTAL_VAT/,
        renderer: totalVatRenderer,
        data: 'TOTAL_VAT',
        meta: {
            className: 'cell-total'
        }
    },

    totalWithVat: {
        pattern: /TOTAL_WITH_VAT/,
        renderer: totalWithVatRenderer,
        data: 'TOTAL_WITH_VAT',
        meta: {
            className: 'cell-total'
        }
    }
});
