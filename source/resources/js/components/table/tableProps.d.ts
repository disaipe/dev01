import type { TableColumnOptions } from '@/types'; 

export type TableFilterProps = {
    filters?: Object
}

export type TableProps = {
    id?: string,
    reference?: string,
    columns?: TableColumnOptions[],
    items?: any[],
    context?: Object,
    canLoad?: boolean,
    canCreate?: boolean,
    canUpdate?: boolean,
    canDelete?: boolean
} & TableFilterProps