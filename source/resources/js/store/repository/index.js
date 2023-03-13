import { useRepo } from 'pinia-orm';
import Repository from './repository';
import Models from '../models';
import CoreModel from '../model';

const repos = {};

export function defineModel(name, options = {}){
    return ({
        [name]: class extends CoreModel {
            static entity = options.entity;
            static eagerLoad = options.eagerLoad;

            static fields() {
                return options?.fields.call(this) || {};
            }
        }
    })[name];
}

export function defineRepo(model) {
    const repo = useRepo(Repository);
    repo.initialize(model);
    repo.database.register(repo.getModel());
    repos[model.name] = repo;
}

for (const model of Object.values(Models)) {
    defineRepo(model);
}

export function useRepos() {
    return repos;
}

export default Repository;
