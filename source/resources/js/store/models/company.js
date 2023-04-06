import Model from '../model';

export default class Company extends Model {
    static name = 'Company';
    static entity = 'companies';

    static fields() {
        return {
            id: this.uid(),
            code: this.string(''),
            name: this.string(''),
            fullname: this.string(''),
            identity: this.string(''),
            description: this.string('')
        };
    }
}
