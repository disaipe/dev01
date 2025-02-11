import type {
  Cell as ExcelCell,
  Column as ExcelColumn,
  Range as ExcelRange,
  Row as ExcelRow,
  Worksheet as ExcelWorksheet,
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
