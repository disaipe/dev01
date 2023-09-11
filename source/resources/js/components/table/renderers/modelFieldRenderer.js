import VXETable from 'vxe-table';

import columnFieldRenderer from '../columnFieldRenderer';

VXETable.renderer.add('model-field', {
    renderDefault(renderOpts, params) {
        const { row, column } = params;
        const { field } = column;
        const { fields } = renderOpts;
        const value = row[field];
        const type = fields[field]?.type;

        let rendererType = 'raw';

        if (fields[field]?.relation && value) {
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

        return (columnFieldRenderer[rendererType] || columnFieldRenderer['raw'])(value, row, field, fields);
    }
});
