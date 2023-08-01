<?php

namespace App\Modules\Sharepoint\Services;

use App\Modules\Sharepoint\Core\SharepointList;
use App\Modules\Sharepoint\Models\SharepointList as SharepointListModel;
use App\Services\ReferenceService;
use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SharepointService
{
    protected const CONNECTION_NAME = 'sharepoint';

    protected Connection $connection;

    public function __construct() {
        $this->connection = $this->getConnection();
    }

    public function getList(string $name, string $site = null): ?SharepointList
    {
        $site = $site ?? '';

        $result = $this->connection->selectOne("
            select
                list.tp_ID as id,
                list.tp_fields as fields
            from AllLists list
            left join AllWebs web on list.tp_WebId = web.Id
            where
                convert(varchar(max), tp_Title) = '{$name}'
                and (
                     web.FullUrl = '{$site}'
                    or '{$site}' = ''
                )
        ");

        if (!$result) {
            return null;
        }

        return new SharepointList($result->id, $result->fields);
    }

    public function getConnection(): Connection
    {
        return DB::connection(self::CONNECTION_NAME);
    }

    public static function syncList(SharepointListModel $sharepointList): ?array
    {
        $service = new static();

        $syncFields = Arr::get($sharepointList->options, 'fields');

        if (! ($syncFields && is_array($syncFields))) {
            return null;
        }

        $list = $service->getList($sharepointList->list_name, $sharepointList->list_site);

        if ($list) {
            $aliases = Arr::pluck($syncFields, 'ref', 'field');
            $list->setFieldAliases($aliases);
            $items = $list->getItems($service->getConnection());

            $onlyFields = array_values($aliases);

            $itemsToSync = [];
            foreach ($items as $item) {
                $itemsToSync []= Arr::only((array)$item, $onlyFields);
            }

            $reference = ReferenceService::getModelFromCustom($sharepointList->reference);
            $reference::query()->truncate();
            $reference::query()->insert($itemsToSync);

            return $itemsToSync;
        }

        return null;
    }
}
