import { isServiceCountCell, isServiceNameCell, isServicePriceCell } from './cellTypes';
import { useRepos } from '../../store/repository';

const { Service } = useRepos();

export function getServiceFromCellValue(cellValue) {
    if (cellValue && typeof(cellValue) === 'string') {
        const [, id] = cellValue.split('#');

        if (id) {
            return Service.find(parseInt(id));
        }
    }

    return null;
}

export function serviceNameCellRenderer(instance, td, row, column, prop, value, cellProperties) {
    if (isServiceNameCell(value)) {
        const service = getServiceFromCellValue(value);
        if (service) {
            td.innerText = service.$getName();
        }
    }

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function serviceCountCellRenderer(instance, td, row, column, prop, value, cellProperties) {
    if (isServiceCountCell(value)) {
        td.innerText = 1;
    }

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function servicePriceCellRenderer(instance, td, row, column, prop, value, cellProperties) {
    if (isServicePriceCell(value)) {
        td.innerText = 1;
    }

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function priceValueRenderer(instance, td, row, column, prop, value, cellProperties) {
    if (!value) {
        td.style.backgroundColor = '#FF000011';
    }

    td.innerText = value;
}
