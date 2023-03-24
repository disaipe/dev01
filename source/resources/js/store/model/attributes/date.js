import { Type } from 'pinia-orm';

export default class Date extends Type {
    /**
     * Create a new Datetime attribute instance
     *
     * @param model
     * @param value
     */
    constructor(model, value) {
        super(model, value)
    }

    /**
     * Make the value for the attribute.
     * @param value
     * @returns {string|null}
     */
    make(value) {
        return this.makeReturn('string', value);
    }
}
