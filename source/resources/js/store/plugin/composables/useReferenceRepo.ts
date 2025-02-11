import type { Model } from '../model';
import { getActivePinia } from 'pinia';
import { useRepo } from 'pinia-orm';
import { ReferenceRepository } from '../repository';

export function useReferenceRepo(model: typeof Model) {
  const pinia = getActivePinia();
  ReferenceRepository.useModel = model;
  return useRepo(ReferenceRepository<Model>, pinia);
}
