import type { Model, TypeDefault } from 'pinia-orm';
import { Type } from 'pinia-orm';

export default class Datetime extends Type {
    /**
     * Create a new Datetime attribute instance
     *
     * @param model
     * @param value
     */
    constructor(model: Model, value: TypeDefault<string>) {
        super(model, value)
    }

    /**
     * Make the value for the attribute.
     * @param value
     * @returns {string|null}
     */
    make(value: any): string | null {
        return this.makeReturn<string | null>('string', value);
    }
}
