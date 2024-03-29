import Model from '../model';

export default class Indicator extends Model {
    static name = 'Indicator';
    static entity = 'indicators';
    static primaryKey = 'code';

    static fields() {
        return {
            code: this.string(null),
            name: this.string(null)
        };
    }
}
