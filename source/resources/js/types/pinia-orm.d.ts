import type Date from '@/store/model/attributes/date';
import type Datetime from '@/store/model/attributes/datetime';
import type { AxiosInstance } from 'axios';

import type { Model } from 'pinia-orm';
import type { ModelSchema } from './model';

declare module 'pinia-orm' {
  export interface Attribute extends piniaORM.Attribute {
    name?: string;
  }
}

export { };
