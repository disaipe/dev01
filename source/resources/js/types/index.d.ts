import type { Cell } from 'exceljs';
import type { Model, ModelFields, PrimaryKey } from 'pinia-orm';
import type { Response } from 'redaxios';
import Handsontable from 'handsontable';

export interface IModelOptions {
    entity?: string;
    eagerLoad?: string;
    displayField?: string;
    fields: { [key: string]: string[] }
}

export type ModelFieldRelation = {
    type: string;
    key: PrimaryKey;
    ownerKey?: PrimaryKey;
    model: string;
    multiple: boolean;
    pivot: Model;
}

export type ModelFieldSchema = {
    field: string;
    label: string;
    hidden: boolean;
    visible: boolean;
    pinia: string[];
    readonly : boolean;
    relation: any;
    rules: string;
    type: string;
    lazy: boolean;
    options?: any[];
    filter: [string, ...any[]];
}

export type ModelSchema = {
    [key: string]: ModelFieldSchema;
}

export type ResponseData<T> = {
    status: boolean;
    data: T;
}

export type ResponseBase<T=any> = Response<ResponseData<T>>;

export type HistoryRecord = {
    action: string;
    data: {
        changes: any;
        original: any;
    },
    datetime: string;
    user: string;
};

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

export type SortOrder = 'asc' | 'desc' | '' | null;

export type TableColumnOptions = {
    field: string;
    label?: string;
    hidden?: boolean;
    visible?: boolean;
};