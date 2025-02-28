import type Date from '@/store/model/attributes/date';
import type Datetime from '@/store/model/attributes/datetime';
import type { AxiosInstance } from 'axios';

import type { Attribute as OrigAttribute, Model as OrigModel } from 'pinia-orm';
import type { ModelSchema } from './model';

declare module 'pinia-orm' {
  export interface Attribute extends OrigAttribute {
    name?: string;
  }

  export interface Model extends OrigModel {
    /** API path prefix */
    apiPrefix: string;

    $self: () => Model;
  }
}

export { };
