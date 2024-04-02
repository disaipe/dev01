import type {
    Cell as ExcelCell,
    Column as ExcelColumn,
    Row as ExcelRow,
    Worksheet as ExcelWorksheet,
    Range as ExcelRange
} from 'exceljs';

type WorkSheetColumn = Partial<ExcelColumn>;
type WorkSheetRow = Partial<ExcelRow>;

declare module 'exceljs' {
    export interface Worksheet extends ExcelWorksheet {
        _columns?: WorkSheetColumn[];
        _rows?: WorkSheetRow[];
        _merges?: Record<string, ExcelRange>;
    };
}