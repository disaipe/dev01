import Handsontable from 'handsontable';
import { ContextMenu } from 'handsontable/plugins/contextMenu';
import type { SpreadsheetCellContextMenuSettings, SpreadsheetCellContextMenuItem } from '@/types';

function extractService(instance: Handsontable, row: number, col: number) {
    const meta = instance.getCellMeta(row, col);

    if (meta.original && typeof meta.original === 'string') {
        const match = meta.original.match(/SERVICE#(?<SERVICE_ID>\d+)#.+/);

        if (match?.groups?.SERVICE_ID) {
            return match.groups.SERVICE_ID;
        }
    }

    return null;
}

export default <SpreadsheetCellContextMenuSettings>{
    items: {
        debug: <SpreadsheetCellContextMenuItem>{
            name: 'Отладка',
            hidden() {
                const selectedLast = this.getSelectedLast();

                if (!selectedLast) {
                    return true;
                }

                const [row, col] = selectedLast;

                const serviceId = extractService(this, row, col);
                return !serviceId;
            },
            callback(key, selection) {
                const firstSelected = selection[0].start;
                const serviceId = extractService(this, firstSelected.row, firstSelected.col);

                /* @ts-ignore */
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
