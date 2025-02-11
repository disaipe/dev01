import type { IModelOptions } from '@/types';
import type { ReferenceRepository } from './repository';
import { useReferenceRepo } from './composables/useReferenceRepo';
import { Model } from './model';

const repos: Record<string, ReferenceRepository> = {};

/**
 * Define the new model
 *
 * @param name name of the model
 * @param options model definition
 * @returns new model class
 */
export function defineModel(name: string, options: IModelOptions): typeof Model {
  return ({
    [name]: class extends Model {
      static name = name;
      static entity = options.entity || name;

      eagerLoad = options.eagerLoad;

      $getDisplayField() {
        return options.displayField || super.$getDisplayField();
      }
    },
  })[name];
}

/**
 * Define and initialize new repository for the model
 *
 * @param model repository model
 */
export function defineRepo(model: typeof Model) {
  const repo = useReferenceRepo(model);
  repos[model.name] = repo;
}

export function useRepos() {
  return repos;
}
