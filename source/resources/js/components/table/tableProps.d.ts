import type { TableColumnOptions } from '@/types';

export interface TableFilterProps {
  filters?: object;
}

export type TableProps = {
  id?: string;
  reference?: string;
  columns?: TableColumnOptions[];
  items?: any[];
  context?: object;
  canLoad?: boolean;
  canCreate?: boolean;
  canUpdate?: boolean;
  canDelete?: boolean;
} & TableFilterProps;
