import type { VxeColumnPropTypes } from 'vxe-table'
import type { ModelSchema } from './model';

declare module 'vxe-table' {
    export namespace VxeColumnPropTypes {
        interface CellRender extends VxeColumnPropTypes.CellRender {
            fields: ModelSchema;
        }
    }
}

export {}