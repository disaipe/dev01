<?php

namespace App\Core\Reference;

use App\Exports\ReferencesExport;
use App\Http\Requests\ReferenceListingRequest;
use App\Http\Requests\ReferencePushRequest;
use App\Http\Resources\ProtocolRecordResource;
use App\Http\Resources\ReferenceRecordResource;
use App\Http\Resources\ReferenceRecordsCollection;
use App\Models\ProtocolRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Excel;

class ReferenceController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected string|ReferenceModel $model;

    protected ReferenceEntry $reference;

    public static function fromReference($reference): ReferenceController
    {
        return new class($reference) extends ReferenceController
        {
            public function __construct($reference)
            {
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

        // orders input
        $sorts = $request->input('order');

        // pagination options
        $page = $request->input('page');
        $perPage = $request->input('perPage', 100);

        // make query
        $query = $this->reference->query();

        if ($id) {
            $query->whereKey($id);
        }

        if ($filters) {
            $query->filter();
        }

        if ($sorts) {
            $this->applySort($query, $sorts);
        }

        $paginator = $query->paginate(
            $perPage,
            $query->qualifyColumn('*'),
            'page',
            $page
        );

        return new JsonResponse([
            'status' => true,
            'data' => ReferenceRecordsCollection::make($paginator->items()),
            'total' => $paginator->total(),
        ]);
    }

    public function push(ReferencePushRequest $request): JsonResponse
    {
        $body = $request->post();

        $model = $this->getModel();
        $keyName = $model->getKeyName();
        $key = Arr::get($body, $keyName);

        /** @var ReferenceModel $record */
        $record = $this->getModel()->newQuery()->updateOrCreate(
            [$keyName => $key],
            $body
        );

        $this->updateRelations($record, $body);

        return new JsonResponse([
            'status' => true,
            'data' => ReferenceRecordResource::make($record),
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

    public function export(Request $request): JsonResponse
    {
        $options = $request->input('options');

        $format = Arr::get($options, 'format');
        $onePage = Arr::get($options, 'one_page') === true;

        // filters input
        $filters = $request->input('filters');

        // orders input
        $sorts = $request->input('order');

        // visible columns
        $columns = $request->input('columns', []);

        // make query
        $query = $this->reference->query();

        if ($onePage) {
            // pagination options
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 100);

            $query->limit($perPage)->offset(($page - 1) * $perPage);
        }

        if ($filters) {
            $query->filter();
        }

        if ($sorts) {
            $this->applySort($query, $sorts);
        }

        // load used relations
        $relations = $this->reference->getModelInstance()->listRelations();

        foreach ($columns as $column) {
            if (array_key_exists($column, $relations)) {
                $query->with($column);
            }
        }

        $rows = $query->get();

        $writerType = match ($format) {
            'xls' => Excel::XLS,
            'csv' => Excel::CSV,
            default => Excel::XLSX,
        };

        $export = new ReferencesExport($this->reference, $rows, $columns);
        $data = $export->raw($writerType);

        return new JsonResponse([
           'status' => true,
           'data' =>  [
               'name' => "export.$format",
               'content' => base64_encode($data)
           ],
        ]);
    }

    public function schema(): JsonResponse
    {
        $schema = $this->reference->getSchema();

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
            $shortModel = class_basename($this->getModel());

            $data = ProtocolRecord::query()
                ->where('object_id', '=', $id)
                ->where('object_type', '=', $shortModel)
                ->orderBy('datetime', 'desc')
                ->get();
        }

        return new JsonResponse([
            'status' => true,
            'data' => ProtocolRecordResource::collection($data),
        ]);
    }

    public function related(Request $request): JsonResponse
    {
        $models = $request->input('models', []);

        $results = [];

        foreach ($models as $model) {
            if (! $modelInstance = resolveModel($model)) {
                continue;
            }

            $relatedMethod = 'asRelated';

            if (method_exists($modelInstance, $relatedMethod)) {
                $results[$model] = $modelInstance->$relatedMethod();
            } else {
                $results[$model] = $modelInstance->newQuery()->get()->toArray();
            }
        }

        return new JsonResponse([
            'status' => true,
            'data' => $results,
        ]);
    }

    public function getModel(): ReferenceModel
    {
        return $this->reference->getModelInstance();
    }

    protected function applySort(Builder $query, array $sort): void
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        $model = $this->getModel();

        foreach ($sort as $field => $order) {
            $isRelation = $model->isRelation($field);

            if ($isRelation) {
                /** @var Relation $relation */
                $relation = $model->$field();

                $relatedModel = $relation->getModel();
                $relatedTable = $relatedModel->getTable();

                /*
                 * Here we can get reference only by table name, because
                 * custom references has one class name and no other
                 * properties to identify the model/reference
                 */
                $reference = $references->getByTableName($relatedTable);
                $orderBy = $reference->getPrimaryDisplayField() ?? $relatedModel->getKeyName();

                $query
                    ->leftJoin(
                        $relatedTable,
                        $relation->getQualifiedOwnerKeyName(),
                        '=',
                        $relation->getQualifiedForeignKeyName()
                    )
                    ->orderBy($relatedModel->qualifyColumn($orderBy), $order)
                    ->select($query->qualifyColumns($query->getQuery()->getColumns()));
            } else {
                if ($model->isSortable($field)) {
                    $query->orderBy($field, $order);
                }
            }
        }
    }

    protected function updateRelations(ReferenceModel $record, array $body): void
    {
        $relations = $record->listRelations();

        foreach ($relations as $name => $type) {
            switch ($type) {
                case BelongsToMany::class:
                    /** @var BelongsTo $relation */
                    $relation = $record->$name();
                    $relatedKey = $relation->getRelated()->getKeyName();
                    $bodyKey = "$name.$relatedKey";

                    if (Arr::has($body, $bodyKey)) {
                        if ($value = Arr::get($body, $bodyKey)) {
                            $relation->sync($value);
                        }
                    }
                    break;

                case BelongsTo::class:
                    if (Arr::has($body, $name)) {
                        $value = Arr::get($body, $name);
                        $record->$name()?->sync($value);
                    }
                    break;
                default:
                    break;
            }
        }
    }
}
