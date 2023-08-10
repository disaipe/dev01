<?php

namespace App\Modules\Sharepoint\Core;

use Illuminate\Support\Arr;

class SharepointList
{
    protected const FIELDS_OFFSET = 14;

    protected string $id;

    protected string $fields;

    protected array $cacheFields;

    protected array $aliases = [];

    public function __construct(string $id, string $fields)
    {
        $this->id = $id;
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        if (isset($this->cacheFields)) {
            return $this->cacheFields;
        }

        $fieldsData = gzinflate(substr($this->fields, self::FIELDS_OFFSET));
        $xml = simplexml_load_string("<root>{$fieldsData}</root>");

        $this->cacheFields = [];
        foreach ($xml->xpath('/root/Field') as $field) {
            $this->cacheFields[] = ((array) $field)['@attributes'];
        }

        return $this->cacheFields;
    }

    public function getItems($conn): mixed
    {
        $queryFieldsStr = $this->getQueryFields();

        return $conn->select("
            select
                tp_ID,
                {$queryFieldsStr}
            from AllUserData
            where
                tp_ListID = '{$this->id}'
                and tp_DeleteTransactionId = ''
        ");
    }

    public function setFieldAlias(string $field, string $alias): void
    {
        $this->aliases[$field] = $alias;
    }

    public function setFieldAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    protected function getQueryFields(): string
    {
        $queryFields = array_reduce($this->getFields(), function (array $acc, array $cur) {
            $column = Arr::get($cur, 'ColName');
            $name = Arr::get($this->aliases, $column, $column);

            if ($column && $name) {
                $acc[] = "{$column} as {$name}";
            }

            return $acc;
        }, []);

        return implode(', ', $queryFields);
    }
}
