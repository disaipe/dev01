export type ModelKey = string | number | null;

export type ModelFieldSchema = {
    field: string;
    label: string;
    hidden: boolean;
    visible: boolean;
    pinia: string[];
    readonly : boolean;
    relation: any;
    rules: string;
    type: string;
    lazy: boolean;
    options?: any[];
    filter: [string, ...any[]];
}

export type ModelSchema = Record<string, ModelFieldSchema>;