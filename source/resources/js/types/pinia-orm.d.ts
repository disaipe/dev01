import * as pinia from 'pinia-orm';

declare module 'pinia-orm' {    
    export interface Attribute extends pinia.Attribute {
        name?: string
    }
}