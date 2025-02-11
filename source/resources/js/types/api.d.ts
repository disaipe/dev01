import type { AxiosResponse as Response } from 'axios';
import type { Element, Model } from 'pinia-orm';
import type { ModelKey, SortStore } from './index';

export type ApiResponseBody<T, A = {}> = {
  status: boolean;
  data: T;
} & A;

export type ResponseBase<T = any> = Response<ApiResponseBody<T>>;
export type ErrorResponse = Response<any>;

export interface InvoiceServiceDebugData {
  service: {
    id: number;
    name: string;
  };
  columns: Record<string, string>;
  rows: Record<string, string | number | boolean | undefined>[];
}
export interface InvoiceData {
  xlsx: string;
  values: Record<string, string | number>;
  errors: { service_id: number; service_name: string; message: string }[];
  debug?: Record<number, InvoiceServiceDebugData>;
}
export type InvoiceResponse = ResponseBase<InvoiceData>;

export type RelatedModelsResponse = ResponseBase<Record<string, Element[]>>;

export interface FetchQueryDownloadOptions {
  format: string;
  one_page: boolean;
}
export interface FetchQueryParams {
  id?: ModelKey;
  filters?: any;
  order?: SortStore;
  page?: number;
  perPage?: number;
  columns?: string[];
  options?: FetchQueryDownloadOptions;
}
export type FetchModelResponse = ResponseBase<Model[], { total?: number }>;
export interface FetchModelResult<M extends Model = Model> {
  response: FetchModelResponse;
  items?: M[];
}

export interface HistoryRecord {
  action: string;
  data: {
    changes: any;
    original: any;
  };
  datetime: string;
  user: string;
}
