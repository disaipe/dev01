import { ref, computed } from 'vue';

import set from 'lodash/set';
import unset from 'lodash/unset';
import cloneDeep from 'lodash/cloneDeep';
import round from 'lodash/round';

import excel from 'exceljs';
import FileSaver from 'file-saver';

import HyperFormula, { AlwaysSparse } from 'hyperformula';

import { registerLanguageDictionary, ruRU } from 'handsontable/i18n';
import { registerAllModules } from 'handsontable/registry';

import { makeMatrix } from '../../utils/arrayUtils';
import { base64ToBuffer } from '../../utils/base64';

registerLanguageDictionary(ruRU);
registerAllModules();

function isFormula(value) {
    return value
        && typeof(value) === 'string'
        && value.startsWith('=');
}

const store = ref({
    spread: null,

    workbook: null,
    worksheet: null,

    merges: [],

    cellModifier: null
});

const worksheet = computed(() => store.value.worksheet);
const instance = computed(() => store.value.spread?.hotInstance);

function setCellPropertyValue(row, col, property, value) {
    set(store.value.worksheet.getCell(row + 1, col + 1), property, value);
}

function setRangePropertyValue(range, property, value) {
    for (let row = range.from.row; row <= range.to.row; row++) {
        for (let col = range.from.col; col <= range.to.col; col++) {
            setCellPropertyValue(row, col, property, value);
        }
    }
}

function removeCellProperty(row, col, property) {
    unset(store.value.worksheet.getCell(row + 1, col + 1), property);
}

function isMerged(row, col) {
    for (const merge of store.value.merges) {
        if (
            row >= merge.top && row <= merge.bottom
            && col >= merge.left && col <= merge.right
        ) {
            return merge;
        }
    }

    return false;
}

export function configure(settings = {}) {
    const hyperFormulaInstance = HyperFormula.buildEmpty({
        licenseKey: 'internal-use-in-handsontable',
        chooseAddressMappingPolicy: new AlwaysSparse()
    });

    const syncData = () => {
        for (let row = 0; row < instance.value.countRows(); row++) {
            for (let col = 0; col < instance.value.countCols(); col++) {
                const xrow = row + 1;
                const xcol = col + 1;

                // check whe cell is not merged, because
                // we need get only first value from merged cell,
                // otherwise next cell overwrites value by nulls
                let merge = isMerged(xrow, xcol)

                if (merge) {
                    if (xrow !== merge.top || xcol !== merge.left) {
                        continue;
                    }
                }

                const value = instance.value.getSourceDataAtCell(row, col);
                const cell = worksheet.value.getCell(xrow, xcol);

                if (isFormula(value)) {
                    cell.value = {
                        formula: value.replace('=', '') // remove first sign
                    };
                } else {
                    cell.value = value;
                }
            }
        }
    };

    // set column widths and row heights (xlsx to Handsontable)
    const syncSizes = () => {
        const toSync = {};

        if (!settings.colWidths) {
            toSync.colWidths = store.value.worksheet._columns.map((column) => column.width * 7.12);
        }

        toSync.rowHeights = store.value.worksheet._rows.map((row) => row.height * 1.33);

        instance.value.updateSettings(toSync);
    };

    /**
     * Manually set row headers height to avoid artifacts with multiline cells
     */
    const updateRulerCellsHeight = () => {
        const table = instance.value.table;
        const tableRowThs = table.querySelectorAll('tbody tr th');

        const leftRowHeaders = instance.value.rootElement.querySelector('.ht_clone_left');
        const leftRowHeaderThs = leftRowHeaders.querySelectorAll('table tbody tr th');

        if (tableRowThs?.length && leftRowHeaderThs?.length) {
            for (let row = 0; row < tableRowThs.length; row++) {
                leftRowHeaderThs[row].style.height = `${tableRowThs[row].clientHeight}px`;
                leftRowHeaderThs[row].dataset.height = `${tableRowThs[row].clientHeight}px`;
            }
        }
    };

    return {
        autoRowSize: true,
        rowHeaders: true,
        colHeaders: true,
        fillHandle: true,
        contextMenu: true,
        comments: true,
        // wordWrap: false,
        manualRowResize: true,
        manualColumnResize: true,
        mergeCells: true,
        outsideClickDeselects: false,
        language: ruRU.languageCode,
        licenseKey: 'non-commercial-and-evaluation',
        formulas: {
            engine: hyperFormulaInstance
        },
        ...settings,
        afterRender() {
            syncData();
        },
        beforeRenderer: defaultRenderer,
        /**
         * Fired after cell merging
         *
         * @param {Object} cellRange Selection cell range
         * @param {Object} mergeParent The parent collection of the provided cell range
         * @param {boolean} auto `true` if called automatically by the plugin
         */
        afterMergeCells(cellRange, mergeParent, auto = false) {
            try {
                worksheet.value.mergeCells(
                    cellRange.from.row + 1,
                    cellRange.from.col + 1,
                    cellRange.to.row + 1,
                    cellRange.to.col + 1
                );
            } catch (e) {
                // console.error(e);
            }
        },
        /**
         * Fired after unmerging the cells
         *
         * @param {Object} cellRange Selection cell range.
         * @param {boolean} auto `true` if called automatically by the plugin.
         */
        afterUnmergeCells(cellRange, auto = false) {
            try {
                worksheet.value.unMergeCells(
                    cellRange.from.row + 1,
                    cellRange.from.col + 1,
                    cellRange.to.row + 1,
                    cellRange.to.col + 1,
                );
            } catch (e) {
                // console.error(e);
            }

            // reset cell meta formatting
            for (let row = cellRange.from.row; row <= cellRange.to.row; row++) {
                for (let col = cellRange.from.col; col <= cellRange.to.col; col++) {
                    const v = instance.value.getSourceDataAtCell(row, col);
                    if (!v) {
                        instance.value.setCellMetaObject(row, col, {
                            renderer: undefined,
                            editor: undefined,
                            selectOptions: undefined,
                            className: undefined
                        });
                    }
                }
            }
        },
        /**
         * Fired after created a new column.
         *
         * @param {number} index Represents the visual index of first newly created column in the data source
         * @param {number} amount Number of newly created columns in the data source
         * @param {?string} source String that identifies source of hook cal
         */
        afterCreateCol(index, amount, source = null) {
            for (let i = 0; i < amount; i++) {
                worksheet.value.spliceColumns(index + 1, 0, []);
            }
            syncSizes();
        },
        /**
         * Fired after created a new row.
         *
         * @param {number} index Represents the visual index of first newly created row in the data source array
         * @param {number} amount Number of newly created rows in the data source array
         * @param {?string} source String that identifies source of hook call
         */
        afterCreateRow(index, amount, source = null) {
            const rows = [];

            for (let i = 0; i < amount; i++) {
                rows.push([]);
            }

            worksheet.value.insertRows(index + 1, rows);
            syncSizes();
        },
        /**
         * Fired after one or more columns are removed
         *
         * @param {number} index Visual index of starter column
         * @param {number} amount An amount of removed columns
         * @param {Array} physicalColumns An array of physical columns removed from the data source
         * @param {?string} source String that identifies source of hook call
         */
        afterRemoveCol(index, amount, physicalColumns, source = null) {
            worksheet.value.spliceColumns(index + 1, amount);
            syncSizes();
        },
        /**
         * Fired after one or more rows are removed.
         *
         * @param {number} index Visual index of starter row
         * @param {number} amount An amount of removed rows.
         * @param {Array} physicalRows An array of physical rows removed from the data source
         * @param {?string} source String that identifies source of hook call
         */
        afterRemoveRow(index, amount, physicalRows, source = null) {
            worksheet.value.spliceRows(index + 1, amount);
            syncSizes();
        },
        /**
         * Fired by ManualColumnResize plugin after rendering the table with modified column sizes.
         *
         * @param {number} newSize Calculated new column width
         * @param {number} column Visual index of the resized column
         * @param {boolean} isDoubleClick Flag that determines whether there was a double click
         */
        afterColumnResize(newSize, column, isDoubleClick) {
            worksheet.value.getColumn(column + 1).width = round(newSize / 7.12, 2);

            updateRulerCellsHeight();
        },
        /**
         * Fired by ManualRowResize plugin after rendering the table with modified row sizes.
         *
         * @param {number} newSize Calculated new row height
         * @param {number} row Visual index of the resized row
         * @param {boolean} isDoubleClick Flag that determines whether there was a double click
         */
        afterRowResize(newSize, row, isDoubleClick) {
            worksheet.value.getRow(row + 1).height = round(newSize / 1.33, 2);
        },
        /**
         * Fired after Handsontable's data gets modified by the loadData() method or the updateSettings() method
         *
         * @param {Array} sourceData An array of arrays, or an array of objects, that contains Handsontable's data
         * @param {boolean} initialLoad A flag that indicates whether the data was loaded at Handsontable's initialization (true) or later (false)
         * @param {?string} source The source of the call
         */
        afterLoadData(sourceData, initialLoad, source) {
            store.value.workbook.removeWorksheet(store.value.worksheet.id);
            store.value.worksheet = store.value.workbook.addWorksheet('Data');
        },
        /**
         * Fired after the updateData() method modifies Handsontable's data.
         *
         * @param {Array} sourceData An array of arrays, or an array of objects, that contains Handsontable's data
         * @param {boolean} initialLoad A flag that indicates whether the data was loaded at Handsontable's initialization (true) or later (false)
         * @param {?string} source The source of the call
         */
        afterUpdateData(sourceData, initialLoad, source) {
            syncData();
            syncSizes();
        },
        /**
         * Fired before aligning the cell contents.
         *
         * @param {Object} stateBefore An object with class names defining the cell alignment.
         * @param {Array} ranges An array of CellRange coordinates where the alignment will be applied.
         * @param {string} type Type of the alignment - either horizontal or vertical.
         * @param {string} alignmentClass String defining the alignment class added to the cell. Possible values:
         *  htLeft, htCenter, htRight, htJustify, htTop, htMiddle, htBottom.
         */
        beforeCellAlignment(stateBefore, ranges, type, alignmentClass) {
            for (const range of ranges) {
                const stylesMap = {
                    htLeft: 'left',
                    htCenter: 'center',
                    htRight: 'right',
                    htTop: 'top',
                    htMiddle: 'middle',
                    htBottom: 'bottom'
                };

                const style = stylesMap[alignmentClass];

                setRangePropertyValue(range, `style.alignment.${type}`, style);
            }

            instance.value.render();
        }
    };
}

export function loadFromBuffer(buffer) {
    instance.value.suspendRender();
    instance.value.loadData([]);

    const workbook = new excel.Workbook();

    workbook.xlsx.load(buffer).then(() => {
        const worksheet = workbook.getWorksheet(1);
        store.value.worksheet = worksheet;

        // set cells
        const data = makeMatrix(Math.max(50, worksheet.rowCount), Math.max(30, worksheet.columnCount));

        for (let row = 0; row < worksheet.rowCount; row++) {
            for (let col = 0; col < worksheet.columnCount; col++) {
                const cell = worksheet.getCell(row + 1, col + 1);

                // clone style object to avoid changing it by reference
                cell.style = cloneDeep(cell.style);

                // apply custom cell modifiers if set
                if (store.value.cellModifier instanceof Function) {
                    store.value.cellModifier(cell);
                }

                // detect composite types like a formulas
                if (cell.value && typeof(cell.value) === 'object') {
                    const { formula } = cell.value;

                    if (formula) {
                        data[row][col] = `=${formula}`;
                    }
                } else {
                    data[row][col] = cell.value;
                }
            }
        }

        instance.value.updateData(data);

        // set merges
        if (worksheet.hasMerges) {
            store.value.merges = [];
            const mergeCells = instance.value.getPlugin('MergeCells');

            // clear old merges
            mergeCells.clearCollections();

            for (const merge of Object.values(worksheet._merges)) {
                store.value.merges.push({
                    top: merge.top,
                    left: merge.left,
                    bottom: merge.bottom,
                    right: merge.right
                });

                mergeCells.merge(
                    merge.top - 1,
                    merge.left - 1,
                    merge.bottom - 1,
                    merge.right - 1
                );
            }
        }
    }).finally(() => {
        instance.value.resumeRender();
    });

    store.value.workbook = workbook;
}

export function loadFromFile(file) {
    return file.raw.arrayBuffer().then((buffer) => {
        return loadFromBuffer(buffer);
    });
}

export function loadFromBase64(base64string) {
    return base64ToBuffer(base64string).then((buffer) => {
        loadFromBuffer(buffer);
    });
}

export function download() {
    store.value.workbook.xlsx.writeBuffer().then(data => {
        const blob = new Blob([data], {
            type: 'application/vnd.ms-excel;charset=utf-8'
        })

        FileSaver.saveAs(blob, `export.xlsx`)
    });
}

export function defaultRenderer(td, row, column, prop, value, cellProperties) {
    const cell = store.value.worksheet.getCell(row + 1, column + 1);

    if (cell) {
        const { alignment, border, fill, font } = cell.style;

        // Defaults
        td.style.verticalAlign = 'bottom';

        if (alignment) {
            const { horizontal, vertical } = alignment;

            if (horizontal) {
                td.style.textAlign = horizontal;
            }

            if (vertical) {
                td.style.verticalAlign = vertical;
            } else {
                td.style.verticalAlign = 'bottom';
            }
        }

        if (font) {
            const { name, size, bold, italic, underline, color } = font;

            name && (td.style.fontFamily = name);
            size && (td.style.fontSize = `${size}pt`);
            bold && (td.style.fontWeight = 'bold');
            italic && (td.style.fontStyle = 'italic');
            underline && (td.style.textDecoration = 'underline');
            color && (td.style.color = getColor(color));
        }

        if (border) {
            const { left, top, right, bottom } = border;

            const addBorder = (direction) => {
                td.classList.add(`border${direction}`);
            }

            left && addBorder('Left');
            top && addBorder('Top');
            right && addBorder('Right');
            bottom && addBorder('Bottom');
        }

        if (fill) {
            const { bgColor, fgColor } = fill;

            fgColor && (td.style.backgroundColor = getColor(fgColor))
        }
    }
}

export function setBold(value = true) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        setRangePropertyValue(range, 'style.font.bold', value);
    }

    instance.value.render();
}

export function setItalic(value = true) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        setRangePropertyValue(range, 'style.font.italic', value);
    }

    instance.value.render();
}

export function setFontSize(value) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        setRangePropertyValue(range, 'style.font.size', value);
    }

    instance.value.render();
}

export function setBorder(value) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        for (let row = range.from.row; row <= range.to.row; row++) {
            for (let col = range.from.col; col <= range.to.col; col++) {
                const borders = {
                    left: false,
                    top: false,
                    right: false,
                    bottom: false
                };

                switch (value) {
                    case 'left':
                        borders.left = true;
                        break;
                    case 'top':
                        borders.top = true;
                        break;
                    case 'right':
                        borders.right = true;
                        break;
                    case 'bottom':
                        borders.bottom = true;
                        break;
                    case 'all':
                        borders.left = true;
                        borders.top = true;
                        borders.right = true;
                        borders.bottom = true;
                        break;
                    case 'none':
                        borders.left = false;
                        borders.top = false;
                        borders.right = false;
                        borders.bottom = false;
                        break;
                    default:
                        break;
                }

                const setBorder = (direction, value) => {
                    if (value) {
                        setCellPropertyValue(row, col, `style.border.${direction}`, {
                            style: 'thin',
                            color: {
                                indexed: 64
                            }
                        });
                    } else {
                        removeCellProperty(row, col, `style.border.${direction}`);
                    }
                }

                setBorder('left', borders.left);
                setBorder('top', borders.top);
                setBorder('right', borders.right);
                setBorder('bottom', borders.bottom);
            }
        }
    }

    instance.value.render();
}

export function setAlign(value) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        const stylesMap = {
            left: 'horizontal',
            center: 'horizontal',
            right: 'horizontal',
            top: 'vertical',
            middle: 'vertical',
            bottom: 'vertical'
        };

        const type = stylesMap[value];

        setRangePropertyValue(range, `style.alignment.${type}`, value);
    }

    instance.value.render();
}

export function setFontFamily(value) {
    const ranges = instance.value.getSelectedRange();

    for (const range of ranges) {
        setRangePropertyValue(range, 'style.font.name', value);
    }

    instance.value.render();
}

export function useHotTable(container, { cellModifier } = {}) {
    store.value.spread = container;
    store.value.cellModifier = cellModifier;

    const workbook = new excel.Workbook();
    const worksheet = workbook.addWorksheet('Template');

    store.value.workbook = workbook;
    store.value.worksheet = worksheet;

    return {
        store,
        instance,

        history: computed(() => instance.value.getPlugin('UndoRedo'))
    };
}

export function getColor(color) {
    if (color.argb) {
        return '#' + color.argb.substring(2,8);
    }

    return null;
}
