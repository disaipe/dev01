import { ContextMenu } from 'handsontable/plugins/contextMenu';

export default {
    items: {
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
