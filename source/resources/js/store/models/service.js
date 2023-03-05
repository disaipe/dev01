import Model from '../model';
import Indicator from './indicator';

export default class Service extends Model {
    static entity = 'services';

    static eagerLoad = [
        'parent'
    ];

    static fields() {
        return {
            id: this.uid(),
            parent_id: this.number(null),
            name: this.string(''),
            display_name: this.string(''),
            tags: this.attr(null),
            indicator_code: this.string(null),

            parent: this.belongsTo(Service, 'parent_id'),
            children: this.hasMany(Service, 'parent_id'),
            indicator: this.belongsTo(Indicator, 'indicator_code', 'code')
        };
    }
}
