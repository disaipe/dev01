import type { Repository as PiniaRepository } from 'pinia-orm';

import { useRepo, Model } from 'pinia-orm';
import Repository from './repository';
import Models from '../models';
import CoreModel from '../model';
import { snake } from '../../utils/stringsUtils';
import type { IModelOptions } from '@/types';

const repos: { [key: string]: Repository } = {};

interface PivotOptions {
    foreignPivotKey: string;
    relatedPivotKey: string;
}

export function defineModel(name: string, options: IModelOptions): any {
    return ({
        [name]: class extends CoreModel {
            static name = name;
            static entity = options.entity || name;

            eagerLoad = options.eagerLoad;

            $getDisplayField() {
                return options.displayField || super.$getDisplayField();
            }
        }
    })[name];
}

export function definePivot(name: string, options: PivotOptions) {
    return ({
        [name]: class extends Model {
            static isPivot = true;
            static entity = snake(name);
            static primaryKey = [options.foreignPivotKey, options.relatedPivotKey];

            static fields() {
                return {
                    [options.foreignPivotKey]: this.number(null),
                    [options.relatedPivotKey]: this.number(null)
                };
            }
        }
    })[name];
}

export function defineRepo(model: any) {
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
