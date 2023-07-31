<?php

namespace App\Services;

use App\Core\Enums\CustomReferenceContextType;
use App\Models\CustomReference;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomReferenceTableService
{
    protected const TABLE_PREFIX = 'ref_';

    public function sync(CustomReference $reference): void
    {
        $tableName = self::getTableName($reference->name);

        $schemaManager = DB::getDoctrineSchemaManager();
        $comparator = $schemaManager->createComparator();

        $table = new Table($tableName);

        // company context - column and foreign key
        if ($reference->company_context) {
            if ($reference->context_type === CustomReferenceContextType::Code->value) {
                $column = $table->addColumn('company_code', 'string', ['length' => 16]);
                $column->setNotnull(false);
            } else {
                $column = $table->addColumn('company_id', 'bigint', ['unsigned' => true]);
                $column->setNotnull(false);

                $table->addForeignKeyConstraint(
                    'companies',
                    ['company_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE']
                );
            }
        }

        if (is_array($reference->schema)) {
            $fields = Arr::get($reference->schema, 'fields', []);

            foreach ($fields as $field) {
                $name = Arr::get($field, 'name');
                $type = Arr::get($field, 'type');
                $pk = Arr::get($field, 'pk', false);
                $autoincrement = Arr::get($field, 'autoincrement', false);
                $unsigned = Arr::get($field, 'unsigned', false);
                $nullable = Arr::get($field, 'nullable', true);

                if (! $name || ! $type) {
                    continue;
                }

                $column = null;

                switch ($type) {
                    case 'string':
                        $length = Arr::get($field, 'length') ?? 255;
                        $column = $table->addColumn($name, 'string', ['length' => $length]);
                        break;
                    case 'int':
                    case 'integer':
                        $column = $table->addColumn($name, 'integer', ['unsigned' => $unsigned]);
                        break;
                    case 'bigint':
                        $column = $table->addColumn($name, 'bigint', ['unsigned' => $unsigned]);
                        break;
                    case 'float':
                        $column = $table->addColumn($name, 'float');
                        break;
                    case 'date':
                        $column = $table->addColumn($name, 'date');
                        break;
                    case 'datetime':
                        $column = $table->addColumn($name, 'datetime');
                        break;
                    default:
                        break;
                }

                if ($column) {
                    $pk && $table->setPrimaryKey([$name]);
                    $autoincrement && $column->setAutoincrement(true);
                    $nullable && $column->setNotnull(false);

                    $column->setNotnull(false);
                }
            }
        }

        // Apply table schema to database
        $tableExists = $schemaManager->tablesExist($tableName);

        if ($tableExists) {
            $tables = $schemaManager->listTables();
            $tableDB = Arr::first($tables, fn (Table $t) => $t->getName() === $tableName);

            $diff = $comparator->compareTables($tableDB, $table);

            if (! $diff->isEmpty()) {
                $schemaManager->alterTable($diff);
            }
        } else {
            $schemaManager->createTable($table);
        }
    }

    public static function getTableName(string $basename): string
    {
        return Str::start(Str::slug($basename, '_'), self::TABLE_PREFIX);
    }

    public static function preserveSchemaFields(array $data): array
    {
        $fields = Arr::get($data, 'schema.fields', []);
        $names = Arr::pluck($fields, 'name');

        if (! in_array('id', $names)) {
            array_unshift($fields, [
                'name' => 'id',
                'display_name' => 'ID',
                'type' => 'bigint',
                'unsigned' => true,
                'pk' => true,
                'autoincrement' => true,
                'nullable' => false,
                'readonly' => true,
            ]);
        }

        if (! in_array('created_at', $names)) {
            $fields[] = [
                'name' => 'created_at',
                'display_name' => 'Создано',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        if (! in_array('updated_at', $names)) {
            $fields[] = [
                'name' => 'updated_at',
                'display_name' => 'Изменено',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        if (! in_array('deleted_at', $names)) {
            $fields[] = [
                'name' => 'deleted_at',
                'display_name' => 'Удалено',
                'type' => 'datetime',
                'readonly' => true,
            ];
        }

        Arr::set($data, 'schema.fields', $fields);

        return $data;
    }
}
