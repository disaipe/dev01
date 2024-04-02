import * as pinia from 'pinia-orm';
import type { ModelSchema } from './model';
import type Date from '@/store/model/attributes/date';
import type Datetime from '@/store/model/attributes/datetime';

declare module 'pinia-orm' {    
    export interface Attribute extends pinia.Attribute {
        name?: string
    }

    declare class Model extends pinia.Model {
        eagerLoad?: string;

        static labels(): Record<string, string>;
        static rules(): Record<string, string>;
        static schema(): ModelSchema;
        static datetime(value: string): Datetime;
        static date(value: string): Date;

        $getKey(): ModelKey;
        $getKeyName(): string;
    }

    declare class Repository extends pinia.Repository {
        static fieldsSchema: Record<string, ModelSchema>;
    }
}

export {}