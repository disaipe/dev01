export default {
    data() {
        return {
            menuConfig: {
                body: {
                    options: [
                        [
                            { code: 'onContextRowOpen', name: 'Открыть' },
                            { code: 'onContextRowRemove', name: 'Удалить', disabled: !this.canDelete },
                        ],
                        [
                            { code: 'onContextRowHistory', name: 'История изменений' }
                        ]
                    ]
                }
            }
        };
    },
    methods: {
        onContextMenuClick({ menu, type, row }) {
            if (type !== 'body') {
                return;
            }

            if (this[menu.code] instanceof Function) {
                this[menu.code](row, menu.props);
            } else {
                console.warn(`[Table] Method ${menu.code} not implemented`);
            }
        }
    }
}
