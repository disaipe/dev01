import Model from '../model';
import ServiceProvider from './serviceProvider';

export default class ReportTemplate extends Model {
    static entity = 'report-templates';

    static eagerLoad = [
        'service_provider'
    ];

    static fields() {
        return {
            id: this.uid(),
            name: this.string(''),
            service_provider_id: this.number(null),
            content: this.attr(null),

            service_provider: this.belongsTo(ServiceProvider, 'service_provider_id')
        };
    }
}
