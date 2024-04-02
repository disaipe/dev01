import type { VxeTableDefines } from 'vxe-table';
import type { TableProps } from '../tableProps';

type OnContextMenuClickEvent = { 
    menu: VxeTableDefines.MenuFirstOption;
    type: string;
    row: any;
}

type ContextMenuActionCallback = (row: any, event: OnContextMenuClickEvent) => void;

export function useTableContextMenu(tableId: string, { props }: { props: TableProps}) {
    const menuActions: Record<string, ContextMenuActionCallback> = {};

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

    function setContextMenuAction(code: string, callback: ContextMenuActionCallback) {
        menuActions[code] = callback;
    }

    function onContextMenuClick(event: OnContextMenuClickEvent) {
        const { menu, type, row } = event;

        if (type !== 'body' || ! menu.code) {
            return;
        }

        if (menuActions[menu.code] instanceof Function) {
            menuActions[menu.code](row, event);
        } else {
            console.warn(`[Table] Method ${menu.code} not implemented`);
        }
    };

    return {
        menuConfig,

        setContextMenuAction,
        onContextMenuClick
    };
}

export default useTableContextMenu;