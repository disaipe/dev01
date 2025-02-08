import * as piniaORM from 'pinia-orm';
import type { AxiosInstance } from 'axios';
import type { ModelSchema } from './model';
import type Date from '@/store/model/attributes/date';
import type Datetime from '@/store/model/attributes/datetime';

import CommonModel from '@/store/model/model';
import CommonRepository from '@/store/repository';

declare module 'pinia-orm' {    
    export interface Attribute extends piniaORM.Attribute {
        name?: string
    }

    export interface Model {
        [s: keyof piniaORM.ModelFields]: any;

        eagerLoad?: string;

        static labels(): Record<string, string>;
        static rules(): Record<string, string>;
        static schema(): ModelSchema;
        static datetime(value: string): Datetime;
        static date(value: string): Date;

        api(): AxiosInstance;
        baseURL(): string;

        $self(): Model;
        $isSaved(): boolean;
        $getSingleKey(): ModelKey | undefined;
        $getSingleKeyName(): string;
        $getName(): string;
    }

    export interface Query<M extends CommonModel = CommonModel> extends piniaORM.Query<M> {}
    
    export interface Repository<M extends CommonModel = CommonModel> {
        $self(): typeof CommonRepository<M>;

        schema(): Promise<ModelSchema>;
    }
}

export {}