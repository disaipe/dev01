import type { VxeTableDefines } from 'vxe-table';
import type { TableProps } from '../tableProps';

export function useTableContextMenu(tableId: string, { props }: { props: TableProps}) {
    const menuConfig = {
        body: <VxeTableDefines.MenuOptions>{
            options: [
                [
                    { code: 'onContextRowOpen', name: 'Открыть' },
                    { code: 'onContextRowRemove', name: 'Удалить', disabled: !props.canDelete },
                ],
                [
                    { code: 'onContextRowHistory', name: 'История изменений' }
                ]
            ]
        }
    };

    function onContextMenuClick({ menu, type, row }: { menu: VxeTableDefines.MenuFirstOption, type: string, row: any}) {
        if (type !== 'body' || ! menu.code) {
            return;
        }

        if (this[menu.code] instanceof Function) {
            this[menu.code](row, menu.props);
        } else {
            console.warn(`[Table] Method ${menu.code} not implemented`);
        }
    };

    return {
        menuConfig,

        onContextMenuClick
    };
}

export default useTableContextMenu;