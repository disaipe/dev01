import { ContextMenu } from 'handsontable/plugins/contextMenu';

function extractService(row, col) {
    const meta = this.getCellMeta(row, col);

    if (meta.original) {
        const match = meta.original.match(/SERVICE#(?<SERVICE_ID>\d+)#.+/);

        if (match.groups?.SERVICE_ID) {
            return match.groups?.SERVICE_ID;
        }
    }

    return null
}

export default {
    items: {
        debug: {
            name: 'Отладка',
            hidden() {
                const [row, col] = this.getSelectedLast();

                const serviceId = extractService.call(this, row, col);
                return !serviceId;
            },
            callback(key, selection) {
                const firstSelected = selection[0].start;
                const serviceId = extractService.call(this, firstSelected.row, firstSelected.col);

                this.runHooks('debug', serviceId);
            }
        },

        sp0: ContextMenu.SEPARATOR,

        row_above: {},
        row_below: {},
        col_left: {},
        col_right: {},

        sp1: ContextMenu.SEPARATOR,

        remove_row: {},
        clear_column: {},

        alignment: {},
        cut: {},
        copy: {},
        // borders: {},
        mergeCells: {}
    }
};
