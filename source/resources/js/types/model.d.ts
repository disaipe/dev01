export type ModelKey = string | number | null;

export type ModelFieldRelation = {
    type: string;
    key: ModelKey;
    ownerKey?: ModelKey;
    model: string;
    multiple: boolean;
    pivot: Model;
}

export type ModelFieldSchema = {
    field: string;
    label: string;
    description?: string;
    hidden: boolean;
    visible: boolean;
    pinia: string[];
    readonly : boolean;
    required?: boolean;
    relation?: ModelFieldRelation;
    rules: string;
    type: string;
    lazy: boolean;
    options?: any[];
    filter: [string, ...any[]];
    min?: number;
    max?: number;
}

export type ModelSchema = Record<string, ModelFieldSchema>;