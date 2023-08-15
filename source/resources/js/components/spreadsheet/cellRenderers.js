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
    const service = getServiceFromCellValue(value);
    if (service) {
        td.innerText = service.$getName();
    }

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function serviceCountCellRenderer(instance, td, row, column, prop, value, cellProperties) {
    td.innerText = 'КОЛ-ВО';

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function servicePriceCellRenderer(instance, td, row, column, prop, value, cellProperties) {
    td.innerText = 'ЦЕНА';

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function contractNumberRenderer(instance, td, row, column, prop, value, cellProperties) {
    td.innerText = 'ДОГ_НОМ';

    const { className } = cellProperties;

    if (className) {
        td.classList.add(className);
    }
}

export function contractDateRenderer(instance, td, row, column, prop, value, cellProperties) {
    td.innerText = 'ДОГ_ДАТА';

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
