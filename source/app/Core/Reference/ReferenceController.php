<?php

namespace App\Core\Reference;

use App\Http\Requests\ReferenceListingRequest;
use App\Http\Requests\ReferencePushRequest;
use App\Http\Resources\ProtocolRecordResource;
use App\Models\ProtocolRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
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
            $this->applyFilters($query, $filters);
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

        /** @var ReferenceModel $record */
        $record = $this->getModel()->newQuery()->updateOrCreate(
            [$keyName => $key],
            $body
        );

        $this->updateRelations($record, $body);

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
            $modelInstance = resolveModel($model);
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

    protected function applyFilters(Builder $query, array $filters): void
    {
        $referenceFilters = $this->reference->getFilters();

        $query->where(function (Builder $group) use ($filters, $referenceFilters) {
            foreach ($filters as $field => $value) {
                if ($filterQuery = Arr::get($referenceFilters, $field)) {
                    $filterQuery($group, $value, $filters);
                } else if (is_array($value)) {
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
        $schema = $this->reference->getSchema();

        foreach ($body as $field => $value) {
            /** @var ReferenceFieldSchema $fieldSchema */
            $fieldSchema = Arr::get($schema, $field);

            $relation = $fieldSchema->getRelation();

            if ($relation) {
                $record->$relation()?->sync($value);
            }
        }
    }
}
