import { ref, computed } from 'vue';

import set from 'lodash/set';
import cloneDeep from 'lodash/cloneDeep';

import excel from 'exceljs';
import FileSaver from 'file-saver';

import HyperFormula from 'hyperformula';

import { registerLanguageDictionary, ruRU } from 'handsontable/i18n';
import { registerAllModules } from 'handsontable/registry';

import { makeMatrix } from '../../utils/arrayUtils';
import { base64ToBuffer } from '../../utils/base64';
import { isServiceNameCell, isServiceCountCell } from './cellTypes';

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

    cells: [],
    merges: [],

    cellModifier: null
});

const worksheet = computed(() => store.value.worksheet);
const instance = computed(() => store.value.spread?.hotInstance);

function setCellPropertyValue(row, col, property, value) {
    set(store.value.cells, `[${row}][${col}].${property}`, value);
}

function setRangePropertyValue(range, property, value) {
    for (let row = range.from.row; row <= range.to.row; row++) {
        for (let col = range.from.col; col <= range.to.col; col++) {
            setCellPropertyValue(row, col, property, value);
        }
    }
}

export function configure(settings = {}) {
    const hyperFormulaInstance = HyperFormula.buildEmpty({
        licenseKey: 'internal-use-in-handsontable'
    });

    return {
        autoRowSize: true,
        rowHeaders: true,
        colHeaders: true,
        fillHandle: true,
        contextMenu: true,
        comments: true,
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
        beforeChange(changes, source) {
            // change is [row, col, oldVal, newVal]
            for (const change of changes) {
                const [row, col, oldVal, newVal] = change;

                const cell = worksheet.value.getCell(row + 1, col + 1)

                if (isFormula(newVal)) {
                    cell.value = {
                      formula: newVal.replace('=', '') // remove first sign
                    };
                } else {
                    cell.value = newVal;
                }
            }

            settings.beforeChange?.(changes, source);
        },
        beforeRenderer: defaultRenderer,
        afterMergeCells(cellRange, mergeParent, auto) {
            try {
                worksheet.value.mergeCells(
                    cellRange.from.row + 1,
                    cellRange.from.col + 1,
                    cellRange.to.row + 1,
                    cellRange.to.col + 1,
                );
            } catch (e) {
                // console.error(e);
            }
        },
        afterCreateCol(index, amount) {
            for (let i = 0; i < amount; i++) {
                worksheet.value.spliceColumns(index + 1, 0, []);
            }
        },
        afterCreateRow(index, amount) {
            const rows = [];

            for (let i = 0; i < amount; i++) {
                rows.push([]);
            }

            worksheet.value.insertRows(index + 1, rows);
        },
        afterColumnResize(newSize, column) {
            worksheet.value.getColumn(column + 1).width = newSize / 7.12;
        },
        afterRowResize(newSize, row) {
            worksheet.value.getRow(row + 1).height = newSize / 1.33;
        }
    };
}

export function loadFromBuffer(buffer) {
    const workbook = new excel.Workbook();

    workbook.xlsx.load(buffer).then(() => {
        const worksheet = workbook.getWorksheet(1);
        store.value.worksheet = worksheet;

        // set cells
        const data = makeMatrix(50, 30);
        const cells = makeMatrix(50, 30);

        for (let row = 0; row < worksheet.rowCount; row++) {
            for (let col = 0; col < worksheet.columnCount; col++) {
                const cell = worksheet.getRow(row + 1).getCell(col + 1);

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

                cells[row][col] = cell;
            }
        }

        store.value.cells = cells;

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

        // set column widths and row heights
        const colWidths = worksheet._columns.map((column) => column.width * 7.12);
        const rowHeights = worksheet._rows.map((row) => row.height * 1.33);

        instance.value.updateData(data);
        instance.value.updateSettings({ colWidths, rowHeights });
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
    const cell = store.value.cells?.[row]?.[column];

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
                td.style[`border${direction}`] = 'solid 1px black';
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

                const setBorder =  (direction) => {
                    setCellPropertyValue(row, col, `style.border.${direction}`, {
                        style: 'thin',
                        color: {
                            indexed: 64
                        }
                    });
                }

                borders.left && setBorder('left');
                borders.top && setBorder('top');
                borders.right && setBorder('right');
                borders.bottom && setBorder('bottom');
            }
        }
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
