<?php

namespace App\Exports;

use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Core\Reference\ReferenceModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReferencesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    use Exportable;

    protected Collection $collection;

    protected array $columns;

    protected ReferenceEntry $reference;

    protected ReferenceManager $references;

    public function __construct(ReferenceEntry $reference, Collection $collection, array $columns = [])
    {
        $this->reference = $reference;
        $this->collection = $collection;
        $this->columns = $columns;

        $this->references = app('references');
    }

    public function headings(): array
    {
        $headings = [];

        $schema = $this->reference->getSchema();

        foreach ($this->columns as $column) {
            /** @var ?ReferenceFieldSchema $field */
            $field = $schema[$column];

            $headings[] = $field ? $field->getLabel() : $column;
        }

        return $headings;
    }

    public function collection(): Collection
    {
        return $this->collection->map(function (ReferenceModel $record) {
            $rowData = [];

            foreach ($this->columns as $column) {
                $rowData[] = $this->transformValue($record, $column);
            }

            return $rowData;
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    protected function transformValue(ReferenceModel $record, string $attribute): mixed
    {
        if ($record->isRelation($attribute)) {
            $related = $record->$attribute;

            if ($related) {
                $table = $related->getTable();
                $reference = $this->references->getByTableName($table);

                if ($reference && $displayField = $reference->getPrimaryDisplayField()) {
                    return $related->getAttribute($displayField);
                }
            }

            return $related;
        }

        return $record->getAttribute($attribute);
    }
}
