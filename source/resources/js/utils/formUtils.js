export function parseRules(rulesString) {
    if (!rulesString) {
        return {};
    }

    const fieldRules = {
        required: false,
        min: null,
        max: null
    };

    const rules = rulesString.split('|').map((rule) => rule.toLowerCase().trim());

    for (const rule of rules) {
        if (rule === 'required') {
            fieldRules.required = true;
        }

        let test = rule.match(/min:(\d+)/);
        if (test) {
            fieldRules.min = parseInt(test[1]);
        }

        test = rule.match(/max:(\d+)/);
        if (test) {
            fieldRules.max = parseInt(test[1])
        }
    }

    return fieldRules;
}

export function validationRulesFromSchema(schema) {
    if (!schema) {
        return {};
    }

    const formRules = {};

    for (const [key, field] of Object.entries(schema)) {
        const { type, required, min, max } = field;

        const fieldRules = [];

        if (required) {
            fieldRules.push(
                {
                    required: true,
                    message: `Поле "${field.label}" обязательно для заполнения`,
                    trigger: 'blur'
                }
            );
        }

        if (min) {
            fieldRules.push({
                type,
                min,
                message: `Укажите минимум ${min} символов`
            });
        }

        if (max) {
            fieldRules.push({ type, max });
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
