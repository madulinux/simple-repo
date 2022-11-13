<?php

namespace Madulinux\Repositories\Eloquent;

use Closure;
use Exception;
use Illuminate\Container\Container as Application;
use Madulinux\Repositories\Exceptions\GeneralException;
use Madulinux\Repositories\BaseRepositoryInterface;
use Madulinux\Repositories\Criteria\Criteria;
use Madulinux\Repositories\CriteriaInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Class BaseRepository
 * @package Madulinux\Repositories\Eloquent;
 */
abstract class BaseRepository implements BaseRepositoryInterface, CriteriaInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

    /**
     * Prevents fro overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = true;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
        $this->initCriteria();
        $this->boot();
    }

    /**
     * Specify Model class name
     * @return mixed
     */
    abstract public function model();

    /**
     * @return Model|mixed
     * @throws GeneralException
     */
    public function makeModel()
    {
        return $this->model = $this->newModel();
    }

    /**
     * 
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * @return Model|mixed
     * @throws GeneralException
     */
    public function newModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new GeneralException("Class {$this->model()} must be an instace of " . Model::class);
        }

        return $model;
    }

    /**
     * Retrieve data array for populate field select
     *
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function lists($column, $key = null)
    {
        $this->applyCriteria();

        return $this->model->lists($column, $key);
    }

    /**
     * Retrieve data array for populate field select
     * Compatible with Laravel 5.3
     * @param string $column
     * @param string|null $key
     *
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null)
    {
        $this->applyCriteria();

        return $this->model->pluck($column, $key);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->get($columns);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function get($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->get($columns);

        return $result;
    }

    /**
     * @param array $where
     * @param string $column
     * @param bool $reset // clear or reset model and scope
     * 
     * @return int
     */
    public function count(array $where = [], $columns = '*', $reset = true)
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count($columns);

        if ($reset) {
            $this->resetModel();
            $this->resetScope();
        }

        return $result;
    }

    /**
     * Simple pagination
     * @param int $page
     * @param int $per_page
     * @param array $search_fields
     * @param string $search
     * 
     * @return mixed
     */
    public function pagination(int $page = 1, int $per_page = 0, array $search_fields = [], string $search = "")
    {
        $this->applyCriteria();
        $this->applyScope();

        $from = 1;
        $data = $this->model;

        if (!empty($search_fields) && !empty($search)) {
            $data = $data->whereLike($search_fields, $search);
        }

        $total = $data->count();

        $to = $total;
        if ($per_page > 0) {
            $to = $page * $per_page;
            $skip = $to - $per_page;
            $from = $skip + 1;
            $data = $data->skip($skip)->take($per_page);
        }

        $data = $data->get();

        $last_page = (int) ceil($total / $per_page);

        $result = [
            'total'             => $total,
            'per_page'          => $per_page,
            'current_page'      => $page,
            "last_page"         => $last_page,
            "from"              => $from,
            "to"                => $to,
            'data'              => $data,
        ];

        $this->resetModel();
        $this->resetScope();

        return $result;
    }


    /**
     * laravel paginate
     * @param int $length
     * @return mixed
     */
    public function paginate($length)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->paginate($length);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * laravel simplePaginate
     * @param int $length
     * @return mixed
     */
    public function simplePaginate($length)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->simplePaginate($length);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * laravel cursorPaginate
     * @param int $length
     * @return mixed
     */
    public function cursorPaginate($length)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->cursorPaginate($length);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * jquery datatables (datatables.net)
     * @param array $request
     * @return mixed
     */
    public function datatable(array $request)
    {
        $columnDef = isset($request['columnDef']) ? $request['columnDef'] : null;
        $select = $columnDef;
        $columns = $request['columns'];

        $searchable = array();
        $where = [];
        foreach ($columns as $key => $column) {
            if ($columnDef == null && isset($column['name'])) {
                $select[$key] = $column['name'] . ' as ' . $column['data'];
            }

            if (isset($column['search'])) {
                if (isset($column['search']['value'])) {
                    $search_value = $column['search']['value'];
                    $search_regex = $column['search']['regex'];
                    if ($search_value != null || $search_value != "") {
                        if ($search_regex == 'false') {
                            $where[] = [$column['name'] ?? $column['data'], $search_value];
                        } else {
                            if ($search_regex == 'true') {
                                $where[] = [$column['name'] ?? $column['data'], 'like', '%' . $search_value . '%'];
                            } else {
                                $where[] = [$column['name'] ?? $column['data'], 'REGEXP', $search_regex];
                            }
                        }
                    }
                }
            }

            if (isset($column['searchable'])) {
                if ($column['searchable'] == 'true') {
                    $searchable[] = $column['name'] ?? $column['data'];
                }
            }
        }

        $select = $select == null ? ['*'] : $select;

        $start = (int) $request['start'];
        $length = (int) $request['length'];
        $search = (array) $request['search'];
        $order = (array) $request['order'];
        $with = isset($request['with']) ? (array) $request['with'] : [];
        $join = isset($request['join']) ? (array) $request['join'] : [];

        $this->applyCriteria();
        $this->applyScope();

        $data = $this->model;

        $recordsTotal = $data->count();
        $recordsFiltered = $recordsTotal;
        if ($with) {
            $data = $data->with($with);
        }

        $data = $data->select($select);
        if ($join) {
            $this->applyJoin($join);
        }

        if (count($where) != 0) {
            $data = $data->where(function ($query) use ($where) {
                foreach ($where as $key => $value) {
                    if (count($value) == 2) {
                        list($c, $s) = $value;
                        $query = $query->where($c, $s);
                    }
                    if (count($value) == 3) {
                        list($c, $op, $s) = $value;
                        $query = $query->where($c, $op, $s);
                    }
                }
            });
        }

        if (isset($search['value'])) {
            if ($search['value'] != null || $search['value'] != '') {
                $data = $data->where(function ($query) use ($searchable, $search) {
                    foreach ($searchable as $key => $column) {
                        $query = ($key == 0) ? $query->where($column, 'like', '%' . $search['value'] . '%') : $query->orWhere($column, 'like', '%' . $search['value'] . '%');
                    }
                    return $query;
                });
            }
        }

        if (count($where) != 0 || isset($search['value'])) {
            $recordsFiltered = $data->count();
        }

        if ($order) {
            foreach ($order as $k => $ord) {
                $data = $data->orderBy($columns[$ord['column']]['data'], $ord['dir']);
            }
        }


    /**
     * jquery datatables (datatables.net)
     * @param array $request
     * @return mixed
     */
    public function datatableIlike(array $request)
    {
        $columnDef = isset($request['columnDef']) ? $request['columnDef'] : null;
        $select = $columnDef;
        $columns = $request['columns'];

        $searchable = array();
        $where = [];
        foreach ($columns as $key => $column) {
            if ($columnDef == null && isset($column['name'])) {
                $select[$key] = $column['name'] . ' as ' . $column['data'];
            }

            if (isset($column['search'])) {
                if (isset($column['search']['value'])) {
                    $search_value = $column['search']['value'];
                    $search_regex = $column['search']['regex'];
                    if ($search_value != null || $search_value != "") {
                        if ($search_regex == 'false') {
                            $where[] = [$column['name'] ?? $column['data'], $search_value];
                        } else {
                            if ($search_regex == 'true') {
                                $where[] = [$column['name'] ?? $column['data'], 'ilike', '%' . $search_value . '%'];
                            } else {
                                $where[] = [$column['name'] ?? $column['data'], 'REGEXP', $search_regex];
                            }
                        }
                    }
                }
            }

            if (isset($column['searchable'])) {
                if ($column['searchable'] == 'true') {
                    $searchable[] = $column['name'] ?? $column['data'];
                }
            }
        }

        $select = $select == null ? ['*'] : $select;

        $start = (int) $request['start'];
        $length = (int) $request['length'];
        $search = (array) $request['search'];
        $order = (array) $request['order'];
        $with = isset($request['with']) ? (array) $request['with'] : [];
        $join = isset($request['join']) ? (array) $request['join'] : [];

        $this->applyCriteria();
        $this->applyScope();

        $data = $this->model;

        $recordsTotal = $data->count();
        $recordsFiltered = $recordsTotal;
        if ($with) {
            $data = $data->with($with);
        }

        $data = $data->select($select);
        if ($join) {
            $this->applyJoin($join);
        }

        if (count($where) != 0) {
            $data = $data->where(function ($query) use ($where) {
                foreach ($where as $key => $value) {
                    if (count($value) == 2) {
                        list($c, $s) = $value;
                        $query = $query->where($c, $s);
                    }
                    if (count($value) == 3) {
                        list($c, $op, $s) = $value;
                        $query = $query->where($c, $op, $s);
                    }
                }
            });
        }

        if (isset($search['value'])) {
            if ($search['value'] != null || $search['value'] != '') {
                $data = $data->where(function ($query) use ($searchable, $search) {
                    foreach ($searchable as $key => $column) {
                        $query = ($key == 0) ? $query->where($column, 'ilike', '%' . $search['value'] . '%') : $query->orWhere($column, 'ilike', '%' . $search['value'] . '%');
                    }
                    return $query;
                });
            }
        }

        if (count($where) != 0 || isset($search['value'])) {
            $recordsFiltered = $data->count();
        }

        if ($order) {
            foreach ($order as $k => $ord) {
                $data = $data->orderBy($columns[$ord['column']]['data'], $ord['dir']);
            }
        }
        
        
        $data = $data->skip($start)->take($length)->get();

        $result = (object) [
            'data'              => $data,
            'recordsTotal'      => $recordsTotal,
            'recordsFiltered'   => $recordsFiltered,
        ];

        $this->resetModel();
        $this->resetScope();

        return $result;
    }


    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $result = $this->model->create($data);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        $result = $this->model;

        foreach ($data as $key => $value) {
            $result->$key = $value;
        }

        $result->save();

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->find($id, $columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->where($field, '=', $value)->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $where
     * @param array $columns
     * @param bool $or
     * @return mixed
     */
    public function findWhere($where, $columns = ['*'], $or = false)
    {
        $this->applyCriteria();
        $this->applyScope();
        $this->applyConditions($where, $or);

        $result = $this->model->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->whereIn($field, $values)->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->whereNotIn($field, $values)->get($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $field
     * @param array $values
     * @param array $columns
     * @return mixed
     */
    public function findWhereBetween($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->whereBetween($field, $values)->get($columns);

        $this->resetModel();

        return $result;
    }


    /**
     * @param array $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->first($columns);

        $this->resetModel();

        return $result;
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function firstOrCreate(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->firstOrCreate($attributes);

        $this->resetModel();

        return $result;
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function firstOrNew(array $attributes = [])
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->firstOrNew($attributes);

        $this->resetModel();

        return $result;
    }

    /**
     * @param array $data
     * @param $id
     * @param string $field
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $this->applyScope();
        $result = $this->model->findOrFail($id);
        foreach ($data as $key => $value) {
            $result->$key = $value;
        }
        $result->save();

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @param string $field
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function updateBy(string $field, $value, array $data)
    {
        $this->applyScope();

        $result = $this->model->where($field, $value)->update($data);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $this->applyScope();

        $result = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id, $force = false)
    {
        $this->applyScope();
        $result = $this->model;
        if ($force) {
            $result = $result->find($id)->forceDelete();
        } else {
            $result = $result->destroy($id);
        }

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * @param array $where
     * @return mixed
     */
    public function deleteWhere(array $where, $force = false)
    {
        $this->applyScope();
        $this->applyConditions($where);

        $result = $this->model;
        if ($force) {
            $result = $result->forceDelete();
        } else {
            $result = $result->delete();
        }

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * Check if entity has relation
     *
     * @param string $relation
     *
     * @return $this
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * @param  mixed $relations
     * @return $this
     */
    public function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    /**
     * @param string $relation
     * @param closure $closure
     *
     * @return $this
     */
    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }


    /**
     * where conditions
     * @param array $conditions
     * @param bool $or
     * 
     * @return $this
     */
    public function whereConditions(array $conditions, bool $or = false)
    {
        $this->applyConditions($conditions, $or);
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);

        return $this;
    }

    /**
     * Set visible fields
     *
     * @param array $fields
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);

        return $this;
    }

    /**
     * @param mixed $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * @return $this
     */
    public function inRandomOrder()
    {
        $this->model = $this->model->inRandomOrder();

        return $this;
    }

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function skip($skip)
    {
        $this->model = $this->model->skip($skip);

        return $this;
    }

    /**
     * @param int $take
     *
     * @return $this
     */
    public function take($take)
    {
        $this->model = $this->model->take($take);
        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->model = $this->model->limit($limit);
        return $this;
    }

    /**
     * initial criteria & set skipCriteria = false
     * @return $this
     */
    public function initCriteria()
    {
        $this->criteria = new Collection();
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status 
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
    }

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $new_criteria)
    {
        $key = $this->criteria->search(function ($item) use ($new_criteria) {
            return (is_object($item) && (get_class($item) == get_class($new_criteria)));
        });

        if (is_int($key)) {
            $this->criteria->offsetUnset($key);
        }

        $this->criteria->push($new_criteria);
        return $this;
    }

    /**
     * @return $this
     */
    public function applyCriteria()
    {
        if ($this->skipCriteria() === true) {
            return $this;
        }

        $criterias = $this->getCriteria();

        if ($criterias) {
            foreach ($criterias as $criteria) {
                if ($criteria instanceof Criteria)
                    $this->model = $criteria->apply($this->model, $this);
            }
        }

        return $this;
    }

    /**
     * Reset Query Scope
     *
     * @return $this
     */
    public function resetScope()
    {
        $this->scopeQuery = null;

        return $this;
    }

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    protected function applyScope()
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    /**
     * Query Scope
     *
     * @param \Closure $scope
     *
     * @return $this
     */
    public function scopeQuery(\Closure $scope)
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    /**
     * @param array $where
     * @return void
     */
    protected function applyConditions(array $where, $or = false)
    {
        foreach ($where as $key => $value) {
            if ($value instanceof \Closure) {
                $this->model = (!$or) ? $this->model->where($value) : $this->model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $this->model = (!$or) ? $this->model->where($field, $operator, $search) : $this->model->orWhere($field, $operator, $search);
                } elseif (count($value) == 2) {
                    list($field, $search) = $value;
                    $this->model = (!$or) ? $this->model->where($field, $search) : $this->model->orWhere($field, $search);
                }
            } else {
                $this->model = (!$or) ? $this->model->where($key, $value) : $this->model->orWhere($key, $value);
            }
        }
    }

    /**
     * return model table name
     */
    protected function getTableName()
    {
        $model = $this->newModel();
        return $model->getTable();
    }

    public function getColumnListing()
    {
        return Schema::getColumnListing($this->getTableName());
    }

    /**
     * return model primary key
     */
    protected function getTableKey()
    {
        $model = $this->newModel();
        return $model->getKeyName();
    }

    /**
     * @param array $join
     * @return void
     */
    protected function applyJoin(array $join)
    {
        $primary_table = $this->getTableName();
        $primary_table_key = $this->getTableKey();
        $secondary_table = null;
        $secondary_table_key = null;
        $join_type = 'left';

        foreach ($join as $key => $value) {
            if (is_array($value)) {

                if (count($value) === 5) {
                    list($join_type, $primary_table, $primary_table_key, $secondary_table, $secondary_table_key) = $value;
                } elseif (count($value) === 4) {
                    list($join_type, $secondary_table, $secondary_table_key, $primary_table_key) = $value;
                } elseif (count($value) === 3) {
                    list($join_type, $secondary_table, $secondary_table_key) = $value;
                } elseif (count($value) == 2) {
                    list($secondary_table, $secondary_table_key) = $value;
                }

                if ($primary_table != null & $secondary_table != null && $secondary_table_key != null) {
                    switch ($join_type) {
                        case 'left':
                            $this->model = $this->model->leftJoin($secondary_table, $secondary_table . '.' . $secondary_table_key, $primary_table . '.' . $primary_table_key);
                            break;
                        case 'right':
                            $this->model = $this->model->rightJoin($secondary_table, $secondary_table . '.' . $secondary_table_key, $primary_table . '.' . $primary_table_key);
                            break;
                        default:
                            $this->model = $this->model->join($secondary_table, $secondary_table . '.' . $secondary_table_key, $primary_table . '.' . $primary_table_key);
                            break;
                    }
                }
            }
        }
    }

    /**
     * boot from children repository
     */
    public function boot()
    {
    }
}
