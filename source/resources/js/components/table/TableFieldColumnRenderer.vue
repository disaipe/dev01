<script>
import { ref, toRef, computed } from 'vue';
import columnFieldRenderer from './columnFieldRenderer';
import filters from '../../plugin/filters';

export default {
    name: 'TableFieldColumn',
    props: {
        row: {
            type: Object,
            required: true
        },
        field: {
            type: String,
            required: true
        },
        fields: {
            type: Object,
            required: true
        }
    },
    setup(props) {
        const row = toRef(props, 'row');
        const field = toRef(props, 'field');
        const fields = toRef(props, 'fields');

        const getValue = () => row.value[field.value];
        let reactiveValue = computed(getValue);

        // Choose renderer by type
        const type = fields.value[field.value]?.type;

        let rendererType = 'raw';

        if (fields.value[field.value]?.relation && getValue()) {
            rendererType = 'relation';
        } else if (type) {
            switch (type) {
                case 'boolean':
                    rendererType = 'switch';
                    break;
                case 'datetime':
                case 'date':
                case 'select':
                case 'password':
                    rendererType = type;
                    break
                default:
                    break;
            }
        }

        // Apply filters
        const filter = fields.value[field.value]?.filter;
        if (Array.isArray(filter)) {
            const [filterName, args] = filter;
            if (filters[filterName]) {
                reactiveValue = computed(() => filters[filterName](getValue(), ...(args || [])))
            }
        }

        return (columnFieldRenderer[rendererType] || columnFieldRenderer['raw'])
            (reactiveValue, props.row, field.value, fields.value);
    }
}
</script>
