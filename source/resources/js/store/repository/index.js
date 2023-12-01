import { useRepo, Model } from 'pinia-orm';
import Repository from './repository';
import Models from '../models';
import CoreModel from '../model';
import { snake } from '../../utils/stringsUtils';

const repos = {};

export function defineModel(name, options = {}) {
    return ({
        [name]: class extends CoreModel {
            static entity = options.entity;
            static eagerLoad = options.eagerLoad;

            static fields() {
                return options.fields?.call?.(this) || {};
            }
        }
    })[name];
}

export function definePivot(name, options = {}) {
    return ({
        [name]: class extends Model {
            static isPivot = true;
            static entity = snake(name);
            static primaryKey = [options.foreignPivotKey, options.relatedPivotKey];

            static fields() {
                return {
                    [options.foreignPivotKey]: this.number(),
                    [options.relatedPivotKey]: this.number()
                };
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
