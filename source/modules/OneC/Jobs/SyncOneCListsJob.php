<?php

namespace App\Modules\OneC\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\OneC\Models\OneCInfoBase;
use App\Support\RpcClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncOneCListsJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $module = $this->getModule();

        $baseUrl = $module->getConfig('base_list.base_url');
        $secret = $module->getConfig('base_list.secret');
        $rawFilePaths = $module->getConfig('base_list.path');

        $filePaths = collect(explode("\n", $rawFilePaths))
            ->map(fn (string $path) => trim($path))
            ->filter(fn (string $path) => strlen($path) > 0);

        $results = [];

        $rpc = RpcClient::make()
            ->setBase($baseUrl)
            ->setSecret($secret)
            ->setPath('/fs/read')
            ->setAuth(Str::random());

        foreach ($filePaths as $file) {
            $response = $rpc->post(['Path' => $file]);
            $content = $response->json('Content');

            if (! $content) {
                $results[$file] = $response->json('Error');

                continue;
            }

            $lines = explode("\n", $content);

            $references = collect([]);
            $currentRef = null;

            foreach ($lines as $line) {
                $strLine = Str::of($line);
                $section = $strLine->match('/\[(.*?)\]/');

                if ($section->isNotEmpty()) {
                    if ($currentRef && $currentRef->name && $currentRef->conn_string) {
                        $references[] = $currentRef;
                    }

                    $currentRef = new OneCInfoBase();
                    $currentRef->name = $section->value();
                    $currentRef->list_path = $file;

                    continue;
                }

                $connectionString = $strLine->match('/^Connect=(.*)$/');

                if ($connectionString->isNotEmpty()) {
                    $currentRef->conn_string = $connectionString->replaceMatches('/\r/', '')->value();

                    preg_match_all(
                        "/(.*?)=(?:\"\"|')?(.*?)(?:\"\"|')?;/",
                        $connectionString->value(),
                        $connectionParams,
                        PREG_UNMATCHED_AS_NULL | PREG_SET_ORDER
                    );

                    if (count($connectionParams)) {
                        foreach ($connectionParams as $connectionParam) {
                            if ($connectionParam[1] && $connectionParam[2]) {
                                $key = Str::lower($connectionParam[1]);
                                $value = Str::of($connectionParam[2])
                                    ->replaceMatches('/["\'\r]/', '')
                                    ->value();

                                switch ($key) {
                                    case 'ref':
                                        $currentRef->ref = $value;
                                        break;
                                    case 'srvr':
                                        $currentRef->server = $value;
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                    }
                }

                $folderString = $strLine->match('/^Folder=(.*)$/');

                if ($folderString->isNotEmpty()) {
                    if ($currentRef) {
                        $currentRef->folder = $folderString->value();
                    }
                }
            }

            if ($currentRef && $currentRef->name && $currentRef->conn_string) {
                $references->push($currentRef);
            }

            DB::transaction(function () use ($references, $file) {
                /** @var OneCInfoBase[] $uniqueRefs */
                $uniqueRefs = $references->unique(fn (OneCInfoBase $item) => $item->server.$item->ref);

                $updatedKeys = [];

                foreach ($uniqueRefs as $row) {
                    $updatedRecord = OneCInfoBase::query()
                        ->withoutSelectExtending()
                        ->updateOrCreate([
                            'server' => $row->server,
                            'ref' => $row->ref,
                        ], $row->toArray());

                    $updatedKeys[] = $updatedRecord->getKey();
                }

                OneCInfoBase::query()
                    ->withoutSelectExtending()
                    ->where('list_path', $file)
                    ->whereKeyNot($updatedKeys)
                    ->delete();
            });

            $results[$file] = count($references);
        }

        return $results;
    }

    public function getDescription(): ?string
    {
        return __('onec::messages.job.list sync.description');
    }
}
