<template lang="pug">
.spreadsheet-wrapper.h-full(ref='wrapper')
    .relative.z-10
        el-config-provider(size='small')
            slot(name='toolbar')
                .flex.space-x-2.mb-1(v-if='showToolbar')
                    el-dropdown(trigger='click')
                        el-button(icon='arrow-down') Действия
                        template(#dropdown)
                            el-dropdown-menu
                                slot(name='actions-menu-items')
                                el-dropdown-item(@click='download') Сохранить в файл
                                el-dropdown-item(@click='uploader.$el.querySelector("input").click()') Загрузить из файла

                    el-select.shrink-0(
                        v-model='data.fontFamilyInput'
                        style='width: 160px'
                        filterable
                        allow-create
                        placeholder=' '
                        @change='setFontFamily($event); syncCellStyling()'
                    )
                        el-option(
                            v-for='fontFamily of defaultFontFamilies'
                            :key='fontFamily'
                            :label='fontFamily'
                            :value='fontFamily'
                        )

                    el-select.shrink-0(
                        v-model='data.fontSizeInput'
                        style='width: 64px'
                        filterable
                        allow-create
                        placeholder=' '
                        @change='setFontSize($event); syncCellStyling()'
                    )
                        el-option(
                            v-for='fontSize of defaultFontSizes'
                            :key='fontSize'
                            :label='fontSize'
                            :value='fontSize'
                        )
                    el-button-group.shrink-0(v-if='instance')
                        //- el-button(@click='history.undo()') Undo
                        //- el-button(@click='history.redo()') Redo
                        el-button(
                            :class='{ active: data.fontItalic }'
                            @click='setItalic(!data.fontItalic); syncCellStyling()'
                        )
                            icon(icon='material-symbols:format-italic-rounded' height='14')
                        el-button(
                            :class='{ active: data.fontBold }'
                            @click='setBold(!data.fontBold); syncCellStyling()'
                        )
                            icon(icon='material-symbols:format-bold-rounded' height='14')
                        el-button(@click='() => borderDrop.handleOpen()')
                            icon(icon='material-symbols:border-all-outline-sharp' height='14')
                            el-dropdown(
                                ref='borderDrop'
                                trigger='click'
                            )
                                span
                                template(#dropdown)
                                    .grid.grid-cols-5.p-1.gap-1
                                        el-link(@click='setBorder("all")')
                                            icon.text-xl(icon='material-symbols:border-all-outline')
                                        el-link(@click='setBorder("inner")' disabled)
                                            icon.text-xl(icon='material-symbols:border-inner')
                                        el-link(@click='setBorder("horizontal")' disabled)
                                            icon.text-xl(icon='material-symbols:border-horizontal')
                                        el-link(@click='setBorder("vertical")' disabled)
                                            icon.text-xl(icon='material-symbols:border-vertical')
                                        el-link(@click='setBorder("outer")' disabled)
                                            icon.text-xl(icon='material-symbols:border-outer')

                                        el-link(@click='setBorder("left")')
                                            icon.text-xl(icon='material-symbols:border-left')
                                        el-link(@click='setBorder("top")')
                                            icon.text-xl(icon='material-symbols:border-top')
                                        el-link(@click='setBorder("right")')
                                            icon.text-xl(icon='material-symbols:border-right')
                                        el-link(@click='setBorder("bottom")')
                                            icon.text-xl(icon='material-symbols:border-bottom')
                                        el-link(@click='setBorder("none")')
                                            icon.text-xl(icon='material-symbols:border-clear')


                        el-button(@click='() => alignDrop.handleOpen()')
                            icon(icon='material-symbols:format-align-left-rounded' height='14')

                            el-dropdown(
                                ref='alignDrop'
                                trigger='click'
                            )
                                span
                                template(#dropdown)
                                    el-dropdown-item.space-x-2(@click='setAlign("left")')
                                        icon(icon='material-symbols:format-align-left-rounded' height='14')
                                        span По левому краю
                                    el-dropdown-item.space-x-2(@click='setAlign("center")')
                                        icon(icon='material-symbols:format-align-center-rounded' height='14')
                                        span По центру
                                    el-dropdown-item.space-x-2(@click='setAlign("right")')
                                        icon(icon='material-symbols:format-align-right-rounded' height='14')
                                        span По правому краю
                                    el-dropdown-item.space-x-2(@click='setAlign("top")' divided)
                                        icon(icon='material-symbols:vertical-align-top-rounded' height='14')
                                        span По верхнему краю
                                    el-dropdown-item.space-x-2(@click='setAlign("middle")')
                                        icon(icon='material-symbols:vertical-align-center-rounded' height='14')
                                        span По середине
                                    el-dropdown-item.space-x-2(@click='setAlign("bottom")')
                                        icon(icon='material-symbols:vertical-align-bottom-rounded' height='14')
                                        span По нижнему краю

                        slot(name='toolbar-actions')

                    .flex-1
                        slot(name='toolbar-center')

                    el-button-group
                        slot(name='toolbar-extra-actions')

    el-upload.hidden(
        ref='uploader'
        accept='.xlsx'
        :limit='1'
        :on-change='upload'
        :auto-upload='false'
        :show-file-list='false'
    )

    .spreadsheet-container.pb-8
        hot-table.hot-table.bg-gray-100(ref='spread' :settings='hotSettings')

        .flex.items-center.p-1(style='background: #f0f0f0')
            el-select(
                v-model='store.activeSheet'
                style='width: 240px'
                size='small'
                @change='openWorkSheet'
            )
                template(#prefix)
                    icon(icon='tabler:menu-2')

                el-option(
                    v-for='sheet of worksheets'
                    :label='sheet.name'
                    :value='sheet.id'
                )
</template>

<script setup lang="ts">
import type { UploadFile } from 'element-plus';

import { ref, toRef, reactive, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import HotTable from '@handsontable/vue3';

import { parseSize, pxToPt } from '@/utils/cssUtils';

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
    setAlign,

    useHotTable
} from './xlsxUtils';

interface SpreadsheetProps {
    showToolbar?: boolean;
    settings?: Object;
    cellModifier?: (cell: Object) => void;
    defaultSheetName?: string;
    fit?: string;
}

interface Data {
    fontBold: boolean | null;
    fontItalic: boolean| null;
    fontFamilyInput: string| null;
    fontSizeInput: number| null;
}

const defaultFontFamilies = ['Arial', 'Calibri', 'Courier New', 'Helvetica', 'Verdana'];
const defaultFontSizes = [8,9,10,11,12,14,16,18,22,24,26,36,42];

const props = withDefaults(defineProps<SpreadsheetProps>(), {
    showToolbar: true
});

const emit = defineEmits(['debug']);

const upload = (file: UploadFile) => {
    if (file.raw) {
        loadFromFile(file.raw).then(() => {
            uploader.value.clearFiles();
        });
    }
};

const settingsProp = toRef(props, 'settings');
const cellModifier = toRef(props, 'cellModifier');
const defaultSheetName = toRef(props, 'defaultSheetName');
const fit = toRef(props, 'fit');

const wrapper = ref();
const spread = ref();
const uploader = ref();
const borderDrop = ref();
const alignDrop = ref();

const data = reactive<Data>({
    fontBold: null,
    fontItalic: null,
    fontFamilyInput: null,
    fontSizeInput: null
});

if (fit.value) {
    const fitSpreadsheet = () => {
        const target = document.querySelector(fit.value as string);

        if (target) {
            wrapper.value.style.height = `${target.clientHeight - wrapper.value.offsetTop}px`;
        }
    };

    onMounted(() => {
        fitSpreadsheet();
    });

    window.addEventListener('resize', () => debounce(fitSpreadsheet, 1000));
}

function syncCellStyling(row?: number, column?: number) {
    if (!row || !column) {
        const selection = instance.value?.getSelected();

        if (selection) {
            [[row, column]] = selection;
        }
    }

    if (!row || !column) {
        return;
    }

    const cell = instance.value.getCell(row, column);

    if (! cell) {
        return;
    }

    const styles = window.getComputedStyle(cell);

    // set font size selector value
    {
        const { value, unit } = parseSize(styles.fontSize);

        if (value) {
            data.fontSizeInput = unit === 'px' ? pxToPt(value) : value;
        }
    }

    // set font family selector value
    data.fontFamilyInput = styles.fontFamily;

    // set font bold button active state
    {
        const value = parseInt(styles.fontWeight);
        data.fontBold = value > 400;
    }

    // set font italic button active state
    data.fontItalic = styles.fontStyle === 'italic';
}

const {
    store,
    instance,
    worksheets,
    history,

    download,

    createWorkSheet,
    openWorkSheet,
    setWorkSheetData,
    getWorkSheets,
    fitWorksheetColumnsWidthToContent
} = useHotTable(spread, {
    cellModifier: cellModifier.value,
    defaultSheetName: defaultSheetName.value
});

const hotSettings = configure({
    startRows: 50,
    startCols: 25,
    width: '100%',
    height: '100%',
    tableClassName: 'table',
    afterSelection: (row: number, column: number) => {
      syncCellStyling(row, column);
    },
    ...(settingsProp.value || {})
});

defineExpose({
    instance,

    loadFromBase64,
    loadFromBuffer,

    download,

    createWorkSheet,
    openWorkSheet,
    setWorkSheetData,
    getWorkSheets,
    fitWorksheetColumnsWidthToContent
});
</script>

<script lang="ts">
export default {
    name: 'Spreadsheet'
}
</script>

<style lang="postcss">
@import 'handsontable/dist/handsontable.full.css';
</style>

<style lang="postcss" scoped>
.spreadsheet-wrapper {
    @apply relative pb-4;

    * {
        box-sizing: border-box;
    }

    .el-button {
        &.active {
            @apply bg-gray-200;
        }
    }
}

:deep(.spreadsheet-container) {
    @apply relative h-full;

    .hot-table {
        .ht_clone_top,
        .ht_clone_left {
            z-index: 200;
        }

        .ht_clone_top_left_corner {
            z-index: 201;
        }

        .table {
            font-family: 'Calibri';
            font-size: 11pt;

            th, td {
                position: relative;
            }

            td.borderLeft,
            td.borderTop,
            td.borderRight,
            td.borderBottom {
                overflow: visible;

                &:before {
                    content: '';
                    position: absolute;
                    left: -1px;
                    top: -1px;
                    right: -1px;
                    bottom: -1px;
                    border-style: solid;
                    border-color: black;

                    /*
                        z-index of cell selector editor = 200, borders must be below
                        to avoid conflicts
                     */
                    z-index: 10;
                }
            }

            td.borderLeft:before {
                border-left-width: 1px;
            }

            td.borderTop:before {
                border-top-width: 1px;
            }

            td.borderRight:before {
                border-right-width: 1px;
            }

            td.borderBottom:before {
                border-bottom-width: 1px;
            }
        }
    }
}
</style>
