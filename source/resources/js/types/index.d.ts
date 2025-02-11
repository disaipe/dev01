import type { Model, ModelFields, PrimaryKey } from 'pinia-orm';

export * from './api';
export * from './model';
export * from './pinia-orm';
export * from './spreadsheet';
export * from './vxe-table';

export interface IModelOptions {
  entity?: string;
  eagerLoad?: string;
  displayField?: string;
  fields: Record<string, string[]>;
}

export type SortOrder = 'asc' | 'desc' | '' | null;
export type SortStore = Record<string, SortOrder>;

export interface TableColumnOptions {
  field: string;
  label?: string;
  hidden?: boolean;
  visible?: boolean;
}
