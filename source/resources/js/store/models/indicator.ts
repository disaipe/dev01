import CoreModel from '../model';

export default class Indicator extends CoreModel {
    static entity = 'indicators';
    static primaryKey = 'code';

    declare code: string;
    declare name: string;

    static fields() {
        return {
            code: this.string(null),
            name: this.string(null)
        };
    }
}
