<template lang='pug'>
.spreadsheet-wrapper.h-full
    .relative.z-10
        slot(name='toolbar')
            .flex.space-x-2.mb-1(v-if='showToolbar')
                el-dropdown(trigger='click')
                    el-button(size='small' icon='arrow-down') Действия
                    template(#dropdown)
                        el-dropdown-menu
                            slot(name='actions-menu-items')
                            el-dropdown-item(@click='download') Сохранить в файл
                            el-dropdown-item(@click='uploader.$el.querySelector("input").click()') Загрузить из файла

                el-select.w-32(
                    v-model='data.fontFamilyInput'
                    size='small'
                    filterable
                    allow-create
                    placeholder=' '
                    @change='setFontFamily'
                )
                    el-option(
                        v-for='fontFamily of defaultFontFamilies'
                        :key='fontFamily'
                        :label='fontFamily'
                        :value='fontFamily'
                    )
                el-select.w-16(
                    v-model='data.fontSizeInput'
                    size='small'
                    filterable
                    allow-create
                    placeholder=' '
                    @change='setFontSize'
                )
                    el-option(
                        v-for='fontSize of defaultFontSizes'
                        :key='fontSize'
                        :label='fontSize'
                        :value='fontSize'
                    )
                el-button-group(v-if='instance' size='small')
                    //- el-button(@click='history.undo()') Undo
                    //- el-button(@click='history.redo()') Redo
                    el-button(
                        :class='{ active: data.fontItalic }'
                        @click='setItalic(!data.fontItalic)'
                    )
                        Icon(icon='material-symbols:format-italic-rounded' height='14')
                    el-button(
                        :class='{ active: data.fontBold }'
                        @click='setBold(!data.fontBold)'
                    )
                        Icon(icon='material-symbols:format-bold-rounded' height='14')
                    el-button(@click='() => borderDrop.handleOpen()')
                        Icon(icon='material-symbols:border-all-outline-sharp' height='14')
                        el-dropdown(
                            ref='borderDrop'
                            trigger='click'
                        )
                            span
                            template(#dropdown)
                                .grid.grid-cols-5.p-1.gap-1
                                    el-link(@click='setBorder("all")')
                                        Icon.text-xl(icon='material-symbols:border-all-outline')
                                    el-link(@click='setBorder("inner")' disabled)
                                        Icon.text-xl(icon='material-symbols:border-inner')
                                    el-link(@click='setBorder("horizontal")' disabled)
                                        Icon.text-xl(icon='material-symbols:border-horizontal')
                                    el-link(@click='setBorder("vertical")' disabled)
                                        Icon.text-xl(icon='material-symbols:border-vertical')
                                    el-link(@click='setBorder("outer")' disabled)
                                        Icon.text-xl(icon='material-symbols:border-outer')

                                    el-link(@click='setBorder("left")')
                                        Icon.text-xl(icon='material-symbols:border-left')
                                    el-link(@click='setBorder("top")')
                                        Icon.text-xl(icon='material-symbols:border-top')
                                    el-link(@click='setBorder("right")')
                                        Icon.text-xl(icon='material-symbols:border-right')
                                    el-link(@click='setBorder("bottom")')
                                        Icon.text-xl(icon='material-symbols:border-bottom')
                                    el-link(@click='setBorder("none")')
                                        Icon.text-xl(icon='material-symbols:border-clear')

                    slot(name='toolbar-actions')

                .flex-1
                    slot(name='toolbar-center')

                el-button-group(size='small')
                    slot(name='toolbar-extra-actions')

    el-upload.hidden(
        ref='uploader'
        accept='.xlsx'
        :limit='1'
        :on-change='upload'
        :auto-upload='false'
        :show-file-list='false'
    )

    .spreadsheet-container(:class='{ "pt-8": showToolbar, "pb-8": showToolbar }')
        hot-table.bg-gray-100(ref='spread' :settings='hotSettings')
</template>

<script>
import { ref, toRef, reactive } from 'vue';
import { HotTable } from '@handsontable/vue3';
import 'handsontable/dist/handsontable.full.css';

import { pxToPt } from '../../utils/cssUtils';

import {
    configure,

    loadFromBuffer,
    loadFromBase64,
    loadFromFile,
    download,

    setBold,
    setItalic,
    setFontSize,
    setFontFamily,
    setBorder,

    useHotTable
} from './xlsxUtils';

export default {
    name: 'Spreadsheet',
    components: { HotTable },
    props: {
        showToolbar: {
            type: Boolean,
            default: true
        },
        settings: {
            type: Object,
            default: null
        },
        cellModifier: {
            type: Function,
            default: null
        }
    },
    setup(props) {
        const settingsProp = toRef(props, 'settings');
        const cellModifier = toRef(props, 'cellModifier');

        const spread = ref();
        const uploader = ref();
        const borderDrop = ref();

        const data = reactive({
            fontBold: null,
            fontItalic: null,
            fontFamilyInput: null,
            fontSizeInput: null
        });

        const {
            store,
            instance,
            history
        } = useHotTable(spread, {
            cellModifier: cellModifier.value
        });

        const hotSettings = configure({
            startRows: 50,
            startCols: 25,
            width: '100%',
            height: '100%',
            tableClassName: 'table',
            afterSelection: (row, column, row2, column2, preventScrolling, selectionLayerLevel) => {
                const cell = instance.value.getCell(row, column);
                const styles = cell.computedStyleMap();

                // set font size selector value
                {
                    const { value, unit } = styles.get('font-size');
                    data.fontSizeInput = unit === 'px' ? pxToPt(value) : value;
                }

                // set font family selector value
                {
                    const fontFamily = styles.get('font-family');
                    data.fontFamilyInput = fontFamily.toString();
                }

                // set font bold button active state
                {
                    const { value } = styles.get('font-weight');
                    data.fontBold = value > 400;
                }

                // set font italic button active state
                {
                    const { value } = styles.get('font-style');
                    data.fontItalic = value === 'italic';
                }
            },
            ...(settingsProp.value || {})
        });

        return {
            data,

            defaultFontFamilies: ['Arial', 'Calibri', 'Courier New', 'Helvetica', 'Verdana'],
            defaultFontSizes: [8,9,10,11,12,14,16,18,22,24,26,36,42],

            hotSettings,

            // $refs
            spread,
            uploader,
            borderDrop,

            store,
            instance,

            history,

            setItalic,
            setBold,
            setFontFamily,
            setFontSize,
            setBorder,

            loadFromBuffer,
            loadFromBase64,
            loadFromFile,
            upload: (file) => {
                loadFromFile(file).then(() => {
                    uploader.value.clearFiles();
                });
            },
            download
        };
    }
}
</script>

<style lang='postcss'>
.spreadsheet-wrapper {
    @apply relative;

    .el-button {
        &.active {
            @apply bg-gray-200;
        }
    }
}

.spreadsheet-container {
    @apply
        absolute top-0 bottom-0 left-0 right-0
        text-xs;
}

.table {
    font-family: 'Calibri';
    font-size: 11pt;

    th, td {
        position: relative;
    }
}
</style>