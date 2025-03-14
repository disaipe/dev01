<template lang="pug">
el-popover(
  :visible='isVisible'
  :width='200'
  @show='onShow'
)
  template(#reference)
    el-link(
      :underline='false'
      :type='isSet ? "primary" : "default"'
      @click='toggleFilter'
    )
      icon(:icon='isSet ? "tabler:filter-cancel" : "tabler:filter"')

  .flex.flex-col.space-y-2(ref='filterPopup')
    el-config-provider(size='small')

      //- FILTER TYPE SELECTOR
      el-dropdown(
        v-if='canChangeType'
        trigger='click'
        :teleported='false'
        @command='filterStore.types[remoteField] = $event'
      )
        .flex.items-center.space-x-1.text-xs(class='hover:text-blue-500')
          div {{ filterTypes[filterStore.types[remoteField]] }}
          icon(icon='tabler:chevron-down')
        template(#dropdown)
          el-dropdown-menu
            el-dropdown-item(
              v-for='(type, key) of filterTypes'
              :command='key'
            ) {{ type }}

      //- RELATED SELECTION
      div(v-if='schema && schema.relation')
        el-input.mb-2(
          v-model='relatedOptionsFilter'
          placeholder='Поиск'
          clearable
        )

        el-scrollbar.py-1(max-height='200')
          el-checkbox-group.flex.flex-col(
            v-model='filterStore.inputs[remoteField]'
          )
            el-checkbox(
              v-for='relatedOption of filteredRelatedOptions'
              size='small'
              :value='relatedOption[schema.relation.ownerKey]'
            ) {{ relatedOption.$getName() }}

      //- BOOLEAN INPUT
      el-checkbox-group.flex.flex-col(
        v-else-if='schema && (schema.type === "boolean" || schema.type === "checkbox")'
        v-model='filterStore.inputs[remoteField]'
        :max='1'
      )
        el-checkbox(:value='true') Да
        el-checkbox(:value='false') Нет

      //- PLAIN INPUT
      el-input(
        v-else
        v-model='filterStore.inputs[remoteField]'
      )

      .flex.justify-between
        el-link(
          :underline='false'
          :disabled='filterStore.inputs[remoteField] === undefined'
          @click.stop='applyFilter()'
        )
          .text-xs Сбросить

        el-button(
          @click.stop='applyFilter(filterStore.inputs[remoteField])'
        ) Применить
</template>

<script setup>
import { useRepos } from '@/store';
import { onClickOutside } from '@vueuse/core';
import { computed, inject, ref, toRef } from 'vue';

const props = defineProps({
  field: {
    type: String,
    required: true,
  },
  schema: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['filterChange']);

const { filterStore } = inject('TableInstance');

const filterPopup = ref(null);

const field = toRef(props, 'field');
const schema = toRef(props, 'schema');

const relatedOptions = ref([]);
const relatedOptionsFilter = ref(null);
const relation = ref(schema.value?.relation);

const remoteField = computed(() => {
  if (schema.value) {
    if (schema.value.relation) {
      return `${field.value}.${schema.value.relation.ownerKey}`;
    }
  }

  return field.value;
});

const value = toRef(filterStore.filters, remoteField.value);

const isSet = computed(() => filterStore.filters[remoteField.value]);
const isVisible = computed(() => filterStore.visibility[remoteField.value] || false);
const canChangeType = computed(() => {
  if (schema.value) {
    switch (schema.value.type) {
      case 'string':
      case 'number':
        return true;
      default:
        return false;
    }
  }

  return false;
});

const defaultType = computed(() => {
  if (schema.value) {
    switch (schema.value.type) {
      case 'relation':
        return '$in';
      case 'string':
        return '$contains';
      default:
        break;
    }
  }

  return null;
});

const filteredRelatedOptions = computed(() => {
  if (relatedOptions.value && relatedOptionsFilter.value) {
    const rgx = new RegExp(relatedOptionsFilter.value, 'i');
    return relatedOptions.value.filter(item => rgx.test(item.$getName()));
  }

  return relatedOptions.value;
});

const filterTypes = {
  $contains: 'Содержит',
  $eq: 'Равно',
};

function applyFilter(value = undefined) {
  filterStore.visibility[remoteField.value] = false;

  filterStore.inputs[remoteField.value] = undefined;

  if (value !== undefined) {
    filterStore.filters[remoteField.value] = value;
  }
  else {
    delete filterStore.filters[remoteField.value];
  }

  const type = filterStore.types[remoteField.value];

  emit('filterChange', remoteField.value, value, type);
}

function loadRelatedOptions() {
  if (schema.value?.relation?.model) {
    const relatedRepo = useRepos()[schema.value.relation.model];
    if (relatedRepo) {
      relatedOptions.value = relatedRepo.query().all();
    }
  }
}

function onShow() {
  if (!filterStore.inputs[remoteField.value]) {
    filterStore.inputs[remoteField.value] = filterStore.filters[remoteField.value];
  }

  filterStore.types[remoteField.value] = defaultType.value;

  loadRelatedOptions();
}

function toggleFilter() {
  filterStore.visibility[remoteField.value] = !filterStore.visibility[remoteField.value];
}

function close() {
  filterStore.visibility[remoteField.value] = false;
}

onClickOutside(filterPopup, () => {
  if (isVisible.value) {
    close();
  }
});
</script>
