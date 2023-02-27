<?php

namespace App\Core\Reference;

use App\Http\Requests\ReferenceListingRequest;
use App\Http\Requests\ReferencePushRequest;
use App\Http\Resources\ProtocolRecordResource;
use App\Models\ProtocolRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class ReferenceController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected string|ReferenceModel $model;

    protected ?ReferenceEntry $reference;

    public static function fromModel($model, $reference = null): ReferenceController
    {
        return new class($model, $reference) extends ReferenceController
        {
            public function __construct($model, $reference)
            {
                $this->model = $model;
                $this->reference = $reference;
            }
        };
    }

    public function list(ReferenceListingRequest $request): JsonResponse
    {
        // fast filter by id
        $id = $request->input('id');

        // filters input
        $filters = $request->input('filters');

        // pagination options
        $page = $request->input('page');
        $perPage = $request->input('perPage', 100);

        // tree options
        $isTreeQuery = $request->has('root');
        $root = $request->input('root');
        $treeKey = $request->input('treeKey');
        $treeParentKey = $request->input('treeParentKey');

        // make query
        $model = $this->getModel();
        $query = $model->newQuery();

        if ($id) {
            $query->whereKey($id);
        }

        if ($filters) {
            $this->applyFilters($query, $filters);
        }

        if ($isTreeQuery) {
            $table = $model->getTable();
            $key = $treeKey ?? $model->getKeyName();
            $parentKey = $treeParentKey ?? 'parent_id';

            $query
                ->where($parentKey, $root)
                ->selectRaw("*, (SELECT COUNT(1) FROM `{$table}` AS Q WHERE Q.{$parentKey} = `{$table}`.{$key}) as children_count");

            $data = $query->get();

            $keys = $data
                ->where('children_count', '>', 0)
                ->pluck('children_count', $model->getKeyName());

            return new JsonResponse([
                'status' => true,
                'data' => $data,
                'keys' => $keys,
            ]);
        }

        $paginator = $query->paginate(
            $perPage,
            '*',
            'page',
            $page
        );

        return new JsonResponse([
            'status' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
        ]);
    }

    public function push(ReferencePushRequest $request): JsonResponse
    {
        $body = $request->post();

        $model = $this->getModel();
        $keyName = $model->getKeyName();
        $key = Arr::get($body, $keyName);

        $record = $model->newQuery()->updateOrCreate(
            [$keyName => $key],
            $body
        );

        return new JsonResponse([
            'status' => true,
            'data' => $record,
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $keys = $request->post('key');

        if (! is_array($keys)) {
            $keys = [$keys];
        }

        /** @var ReferenceModel[] $records */
        $records = $this->getModel()->newQuery()->whereKey($keys)->withTrashed()->get();

        $removed = [];
        foreach ($records as $record) {
            try {
                if (method_exists($record, 'trashed')) {
                    $method = $record->trashed() ? 'forceDelete' : 'delete';
                } else {
                    $method = 'delete';
                }

                $record->$method();
                $removed[] = $record->getKey();
            } catch (\Exception|\Error) {
            }
        }

        return new JsonResponse([
            'status' => count($keys) === count($removed),
            'removed' => $removed,
        ]);
    }

    public function schema(): JsonResponse
    {
        $schema = $this->reference
            ? $this->reference->getSchema()
            : [];

        return new JsonResponse([
            'status' => true,
            'data' => $schema,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $id = $request->route('record');
        $data = [];

        if ($id) {
            $shortModel = class_basename($this->model);

            $data = ProtocolRecord::query()
                ->where('object_id', '=', $id)
                ->where('object_type', '=', $shortModel)
                ->orderBy('datetime', 'desc')
                ->get();
        }

        return new JsonResponse([
            'status' => true,
            'data' => ProtocolRecordResource::collection($data)
        ]);
    }

    public function related(Request $request): JsonResponse {
        $models = $request->input('models', []);

        $results = [];

        foreach ($models as $model) {
            /** @var Model $modelInstance */
            $modelInstance = app()->make("App\\Models\\{$model}");
            $results[$model] = $modelInstance->newQuery()->get()->toArray();
        }

        return new JsonResponse([
            'status' => true,
            'data' => $results
        ]);
    }

    public function getModel(): ReferenceModel
    {
        return app()->make($this->model);
    }

    protected function applyFilters(Builder $query, array $filters)
    {
        $query->where(function (Builder $group) use ($filters) {
            foreach ($filters as $field => $value) {
                if (is_array($value)) {
                    $group->whereBetween($field, $value);
                } else {
                    if (is_string($value)) {
                        $group->where($field, 'LIKE', '%'.$value.'%');
                    } else {
                        $group->where($field, '=', $value);
                    }
                }
            }
        });
    }
}
