<?php

namespace App\Modules\OneC\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\OneC\Models\OneCInfoBase;
use App\Support\RpcClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncOneCServerListJob extends ModuleScheduledJob
{
    private string $server;

    public function __construct(string $server)
    {
        parent::__construct();

        $this->server = $server;
    }

    public function work(): ?array
    {
        $module = $this->getModule();

        $baseUrl = $module->getConfig('base_list.base_url');
        $secret = $module->getConfig('base_list.secret');

        preg_match('/(?<host>.+?)(?:-(?<srv>\d{4,}))?:?(?<port>\d{4,})?$/', $this->server, $parts);

        $host = Arr::get($parts, 'host');
        $srv = Arr::get($parts, 'srv', );
        $port = intval(Arr::get($parts, 'port', '1541'));

        if (! $srv && $port !== 1541) {
            $srv = $port - 1;
        }

        $serverListFile = "\\\\{$host}\\C$\\Program Files\\1cv8\\srvinfo{$srv}\\reg_{$port}\\1CV8Clst.lst";

        $rpc = RpcClient::make()
            ->setBase($baseUrl)
            ->setSecret($secret)
            ->setPath('/fs/read')
            ->setAuth(Str::random());

        $response = $rpc->post(['Path' => $serverListFile]);

        $content = $response->json('Content');

        if (! $content) {
            return [
                'error' => $response->json('Error'),
            ];
        }

        $rgx = '/{.{8}-.{4}-.{4}-.{4}-.{12},\"(?\'info_base\'.*?)\",\"(?\'name\'.*?)\",\"(?\'db_type\'.*?)\",\"(?\'db_server\'.*?)\",\"(?\'db_base\'.*?)\"/';
        $matchCount = preg_match_all($rgx, $content, $matches, PREG_SET_ORDER);

        $infoBases = OneCInfoBase::query()
            ->where('server', '=', $this->server)
            ->get()
            ->keyBy(fn (OneCInfoBase $item) => Str::lower($item->ref));

        $savedCount = 0;

        DB::transaction(function () use ($matches, $infoBases, &$savedCount) {
            foreach ($matches as $match) {
                /** @var ?OneCInfoBase $infoBase */
                $infoBase = $infoBases->get(Str::lower($match['info_base']));

                if ($infoBase) {
                    $infoBase->db_type = $match['db_type'];
                    $infoBase->db_server = $match['db_server'];
                    $infoBase->db_base = $match['db_base'];
                    $infoBase->save();

                    $savedCount += 1;
                }
            }
        });

        return [
            'server' => $this->server,
            'parsed' => $matchCount,
            'info_bases_total' => $infoBases->count(),
            'info_bases_updated' => $savedCount,
        ];
    }

    public function getDescription(): ?string
    {
        return __('onec::messages.job.server list sync.description');
    }
}
