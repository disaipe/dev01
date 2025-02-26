import type { ModelItem } from '@/utils/usePage';
import type { Model, ModelFields, PrimaryKey } from 'pinia-orm';

export * from './api';
export * from './model';
export * from './pinia-orm';
export * from './spreadsheet';
export * from './vxe-table';

export interface IModelOptions extends ModelItem { }

export type SortOrder = 'asc' | 'desc' | '' | null;
export type SortStore = Record<string, SortOrder>;

export interface TableColumnOptions {
  field: string;
  label?: string;
  hidden?: boolean;
  visible?: boolean;
}
