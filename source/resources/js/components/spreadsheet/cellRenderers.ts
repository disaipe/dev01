import type Handsontable from 'handsontable';
import { useRepos } from '../../store';

const { Service } = useRepos();

export function getServiceFromCellValue(cellValue: any) {
  if (cellValue && typeof (cellValue) === 'string') {
    const [, id] = cellValue.split('#');

    if (id) {
      return Service.find(Number.parseInt(id));
    }
  }

  return null;
}

export function serviceNameCellRenderer(instance: Handsontable, td: HTMLTableCellElement, row: number, column: number, prop: number, value: any, cellProperties: Handsontable.CellMeta) {
  const service = getServiceFromCellValue(value);

  if (service) {
    td.textContent = service.$getName();
  }

  const { className } = cellProperties;

  if (className) {
    td.classList.add(...[className].flat());
  }
}

export function serviceCountCellRenderer(instance: Handsontable, td: HTMLTableCellElement, row: number, column: number, prop: number, value: any, cellProperties: Handsontable.CellMeta) {
  td.textContent = 'КОЛ-ВО';

  const { className } = cellProperties;

  if (className) {
    td.classList.add(...[className].flat());
  }
}

export function servicePriceCellRenderer(instance: Handsontable, td: HTMLTableCellElement, row: number, column: number, prop: number, value: any, cellProperties: Handsontable.CellMeta) {
  td.textContent = 'ЦЕНА';

  const { className } = cellProperties;

  if (className) {
    td.classList.add(...[className].flat());
  }
}

export function priceValueRenderer(instance: Handsontable, td: HTMLTableCellElement, row: number, column: number, prop: number, value: any, cellProperties: Handsontable.CellMeta) {
  if (!value) {
    td.style.backgroundColor = '#FF000011';
  }

  const { className } = cellProperties;

  if (className) {
    td.classList.add(...[className].flat());
  }

  td.textContent = value;
}
