<?php

namespace App\Modules\FileImport\Services;

use App\Models\Company;
use App\Models\CustomReference;
use App\Modules\FileImport\FileImportCompanySyncType;
use App\Modules\FileImport\Models\FileImport;
use App\Services\ReferenceService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FileImportService
{
    protected Spreadsheet $spreadsheet;

    protected Worksheet $worksheet;

    protected int $headersRowIndex = 1;

    public function __construct(string $path = null)
    {
        if ($path) {
            $this->load($this->preparePath($path));
        }
    }

    public static function make(string $path = null): static
    {
        return new static($path);
    }

    public static function import(FileImport $fileImport): array
    {
        $customReferenceId = $fileImport->custom_reference_id;

        /** @var CustomReference $customReference */
        $customReference = CustomReference::query()->whereKey($customReferenceId)->first();

        $fields = Arr::get($fileImport->options, 'fields', []);
        $columns = Arr::pluck($fields, 'ref', 'file');

        $file = static::make($fileImport->path);
        $data = $file->getFilteredRows(array_keys($columns), $columns);

        $tableColumnTypes = Arr::pluck($customReference->getFields(), 'type', 'name');

        foreach ($data as &$row) {
            foreach ($row as $column => &$value) {
                $columnType = Arr::get($tableColumnTypes, $column);

                switch ($columnType) {
                    case 'int':
                    case 'bigint':
                        if (is_string($value)) {
                            $value = preg_replace('/\D/', '', $value);
                            $value = intval($value);
                        }
                        break;
                    case 'float':
                        if (is_string($value)) {
                            $value = preg_replace('/,/', '.', $value);
                            $value = preg_replace('/[^\d.]+/', '', $value);
                            $value = round(floatval($value), 2);
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        $reference = ReferenceService::getModelFromCustom($customReference);
        $reference::query()->truncate();
        $reference::query()->insert($data);

        $fileImport->discardChanges();
        $fileImport->last_sync = Carbon::now();
        $fileImport->saveQuietly();

        return [
            'count' => count($data),
        ];
    }

    public function load(string $path, string|int $sheet = 0): void
    {
        $this->spreadsheet = IOFactory::load($path);

        if (is_string($sheet)) {
            $this->worksheet = $this->spreadsheet->getSheetByName($sheet);
        } else {
            try {
                $this->worksheet = $this->spreadsheet->getSheet($sheet);
            } catch (\Exception $e) {
                Log::error("[FileImport] Worksheet with index {$sheet} opening error: {$e->getMessage()}");

                $this->worksheet = $this->spreadsheet->getActiveSheet();
            }
        }
    }

    public function getHeaders(bool $keyed = false): array
    {
        $rowIterator = $this->worksheet->getRowIterator($this->headersRowIndex);
        $row = $rowIterator->current();
        $cellIterator = $row->getCellIterator();

        $headers = [];

        foreach ($cellIterator as $cell) {
            if ($keyed) {
                $headers[$cell->getColumn()] = $cell->getValue();
            } else {
                $headers[] = $cell->getValue();
            }
        }

        return $headers;
    }

    public function getRows(): array
    {
        $rowIterator = $this->worksheet->getRowIterator($this->headersRowIndex + 1);

        $data = [];
        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cells = [];

            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }

            $data[] = $cells;
        }

        return $data;
    }

    public function getFilteredRows(array $columns = null, array $mapping = []): array
    {
        $headers = $this->getHeaders(true);

        $columnsToLoad = $columns
            ? Arr::where($headers, fn ($name) => in_array($name, $columns))
            : $headers;

        $columnsKeysToLoad = array_keys($columnsToLoad);

        $rowIterator = $this->worksheet->getRowIterator($this->headersRowIndex + 1);

        $data = [];
        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cells = [];

            foreach ($cellIterator as $cell) {
                $column = $cell->getColumn();
                if (in_array($column, $columnsKeysToLoad)) {
                    $originalColumn = $columnsToLoad[$column];
                    $key = $mapping[$originalColumn] ?? $originalColumn;

                    $cells[$key] = $cell->getValue();
                }
            }

            $data[] = $cells;
        }

        return $data;
    }

    protected function preparePath($path): ?string
    {
        if (! $path) {
            return $path;
        }

        $today = Carbon::today();

        // d - Day of the month, 2 digits with leading zeros, 01 to 31
        // j - Day of the month without leading zeros, 1 to 31
        // m - 	Numeric representation of a month, with leading zeros, 01 to 12
        // n - Numeric representation of a month, without leading zeros, 1 to 12
        // Y - A full numeric representation of a year, at least 4 digits
        // y - A two digit representation of a year
        return Str::of($path)
            ->replaceMatches('/\{(d|j|m|n|Y|y)}/', fn ($m) => $today->format($m[1]))
            ->toString();
    }
}
