<?php

namespace App\Http\Controllers;

use App\Facades\Auth;
use App\Models\ReportTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OnlyOfficeController extends Controller
{
    // TODO
    private $jwt_secret = 'ahSaTh4waeKe4zoocohngaihaub5pu';

    public function getFileInfo(Request $request): JsonResponse
    {
        $id = $request->get('id');

        $reportTemplate = ReportTemplate::query()->whereKey($id)->first();
        $reportVersion = ($reportTemplate->updated_at ?? $reportTemplate->created_at)->format('Ymd-His');

        $payload = $request->all();

        $user = Auth::user();

        Arr::set($payload, 'document', [
            'key' => "ReportTemplate-$id-$reportVersion",
            'fileType' => 'xlsx',
            'title' => $reportTemplate->name,
            'url' => "http://nginx/api/web/office/download?id=$id",
            'permissions' => [
                'chat' => false,
                'comment' => false,
                'protect' => false,
            ]
        ]);

        Arr::set($payload, 'editorConfig', [
            'lang' => 'ru',
            'customization' => [
                'about' => false,
                'comments' => false,
                'help' => false,
                'feedback' => false,
                // 'plugins' => false,

                // 'uiTheme' => "theme-dark",

                'autosave' => false,
                'forcesave' => true,

                'compactHeader' => true,
                'toolbarHideFileName' => true,
                'hideRightMenu' => true,
            ],
            'callbackUrl' => 'http://nginx/api/web/office/cb',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'plugins' => [
                'autostart' => [
                    'asc.{0616AE85-5DBE-4B6B-A0A9-455C4F1503AD}'
                ],
                'pluginsData' => [
                    url('/office/plugins/config.json')
                ],
            ],
        ]);

        $token = \Firebase\JWT\JWT::encode($payload, $this->jwt_secret, 'HS256');

        Arr::set($payload, 'token', $token);

        return new JsonResponse([
            'status' => true,
            'payload' => $payload,
        ]);
    }

    /**
     * Returns file content to load in DocumentServer
     */
    public function getFileContent(Request $request) {
        $id = $request->input('id');

        $reportTemplate = ReportTemplate::query()->whereKey($id)->first();

        if ($reportTemplate) {
            return response(base64_decode($reportTemplate->content));
        }

        return response(null, 404);
    }

    /**
     * https://api.onlyoffice.com/docs/docs-api/usage-api/callback-handler/
     */
    public function callbackAction(Request $request)
    {
        $payload = $request->all();

        $status = Arr::get($payload, 'status');

        if ($status == 2 || $status == 6) {
            $key = Arr::get($payload, 'key');

            if ($key) {
                [$model, $id] = explode('-', $key);

                if ($id) {
                    $reportTemplate = ReportTemplate::query()->whereKey($id)->first();

                    if ($reportTemplate) {
                        $downloadUrl = Arr::get($payload, 'url');

                        $newData = file_get_contents($downloadUrl);

                        if ($newData) {
                            $reportTemplate->content = base64_encode($newData);
                            $reportTemplate->save();
                        }
                    }
                }
            }
        }

        return new JsonResponse([
            'error' => 0,
        ]);
    }
}

