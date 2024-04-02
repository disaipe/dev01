type FieldRule = {
    required: boolean | null;
    message: string | null;
    trigger: string | null;
    min: number | null;
    max: number | null;
    type: string | null;
};

type FormRules = Record<string, FieldRule[]>;

export function parseRules(rulesString: string): FieldRule {
    if (!rulesString) {
        return {} as FieldRule;
    }

    const fieldRules = {
        required: false,
        min: null,
        max: null
    } as FieldRule;

    const rules = rulesString.split('|').map((rule) => rule.toLowerCase().trim());

    for (const rule of rules) {
        if (rule === 'required') {
            fieldRules.required = true;
        }

        let test = rule.match(/min:(\d+)/);
        if (test) {
            fieldRules.min = parseInt(test[1], 10);
        }

        test = rule.match(/max:(\d+)/);
        if (test) {
            fieldRules.max = parseInt(test[1], 10)
        }
    }

    return fieldRules;
}

export function validationRulesFromSchema(schema: object): FormRules {
    if (!schema) {
        return {};
    }

    const formRules: FormRules = {};

    for (const [key, field] of Object.entries(schema)) {
        const { type, required, min, max } = field;

        const fieldRules: FieldRule[] = [];

        if (required) {
            fieldRules.push({
                required: true,
                message: `Поле "${field.label}" обязательно для заполнения`,
                trigger: 'blur'
            } as FieldRule);
        }

        if (min) {
            fieldRules.push({
                type,
                min,
                message: `Укажите минимум ${min} символов`
            } as FieldRule);
        }

        if (max) {
            fieldRules.push({ type, max } as FieldRule);
        }

        if (fieldRules.length) {
            formRules[key] = fieldRules;
        }
    }

    return formRules;
}

export default {
    parseRules,
    validationRulesFromSchema
}
