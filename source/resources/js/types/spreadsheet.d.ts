import type { Cell } from 'exceljs';
import Handsontable from 'handsontable';

export type SpreadSheetSettings = Handsontable.GridSettings & {
    defaultSheetName?: string;
}

export type SpreadsheetAlignmentClass = 'htLeft' | 'htCenter' | 'htRight' | 'htJustify' | 'htTop' | 'htMiddle' | 'htBottom';
export type SpreadsheetAlignment = 'left' | 'center' | 'right' | 'top' | 'middle' | 'bottom';
export type SpreadsheetBorder = 'left' | 'top' | 'right' | 'bottom' | 'all' | 'inner' | 'horizontal' | 'vertical' | 'outer' | 'none';
export type SpreadSheetCell = Cell;
export type SpreadSheetCellModifier = (cell: SpreadSheetCell) => void;
export type SpreadSheetCellTypeRenderer = Handsontable.renderers.BaseRenderer;
export type SpreadSheetCellType = {
    pattern: RegExp,
    meta: Handsontable.CellMeta,
    renderer?: SpreadSheetCellTypeRenderer,
    data?: any
};
export type SpreadsheetCellContextMenuSettings = Handsontable.plugins.ContextMenu.Settings;
export type SpreadsheetCellContextMenuItem = Handsontable.plugins.ContextMenu.MenuItemConfig;