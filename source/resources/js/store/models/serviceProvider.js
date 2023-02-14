import Model from '../model';

export default class ServiceProvider extends Model {
    static entity = 'service-providers';

    static fields() {
        return {
            id: this.uid(),
            name: this.string(''),
            fullname: this.string(''),
            identity: this.string(''),
            description: this.string('')
        };
    }
}
