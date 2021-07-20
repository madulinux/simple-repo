<?php
namespace Madulinux\Repositories\Eloquent;

use Madulinux\Exceptions\GeneralException;
use Madulinux\Repositories\BaseRepositoryInterface;
use Madulinux\Repositories\Criteria\Criteria;
use Madulinux\Repositories\CriteriaInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 * @package Madulinux\Repositories\Eloquent;
 */
abstract class BaseRepository implements BaseRepositoryInterface, CriteriaInterface
{
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
     * Prevents fro overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = true;

    public function __construct()
    {
        $this->makeModel();
        $this->initCriteria();
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
        $model = app()->make($this->model());
        if (! $model instanceof Model) {
            throw new GeneralException("Class {$this->model()} must be an instace of " . Model::class);
        }
        return $this->model = $model;
    }
    /**
     * @param array $columns
     * @param $perPage
     * @param $currentPage
     * @return mixed
     */
    public function all($columns = array('*'), $perPage = null, $currentPage = null)
    {
        $this->applyCriteria();
        $perPage = (int) $perPage;
        $currentPage = (int) $currentPage;
        $result = $this->model;
        if ($perPage != 0) {
            $result = $result->take($perPage);
            if ($currentPage != 0) {
                $skip = $currentPage * $perPage;
                $result = $result->skip($skip);
            }
        }
        return $result->get($columns);
    }

    /**
     * jquery datatables (datatables.net)
     * @param array $columns
     * @param int $start
     * @param int $length
     * @param array $search
     * @param array $order
     * @param array $columnsDef
     * @return mixed
     */
    public function datatable(array $columns, int $start = 0, int $length = 10, array $search, array $order, array $columnsDef = ['*'], $with = [], $joins = [])
    {
        $select = $columnsDef;
        $table_name = $this->model->getTable();
        $table_key = $this->model->getKeyName();
        $table_to_join = null;
        $table_to_join_key = null;
        $join_type = 'leftJoin';
        $data = $this->model;

        if ($with) {
            $data = $data->with($with);
        }

        $data = $data->select($select);
        if ($joins) {
            foreach ($joins as $key => $value) {
                if (is_array($value)) {
                    if (count($value) === 5) {
                        list($join_type, $table_name, $table_to_join, $table_key, $table_to_join_key) = $value;
                    } elseif (count($value) === 4) {
                        list($join_type, $table_to_join, $table_to_join_key, $table_key) = $value;
                    } elseif (count($value) === 3) {
                        list($join_type, $table_to_join, $table_to_join_key) = $value;
                    }
                    if ($table_to_join != null && $table_to_join_key != null) {
                        switch ($join_type) {
                            case 'leftJoin':
                                $data = $data->leftJoin($table_to_join, $table_to_join.'.'.$table_to_join_key, $table_name.'.'.$table_key);
                                break;
                            default:
                                $data = $data->join($table_to_join, $table_to_join.'.'.$table_to_join_key, $table_name.'.'.$table_key);
                                break;
                        }
                    }
                }
            }
        }

        foreach ($columns as $key => $column) {
            if (isset($search['value'])) {
                
            }
        }


        if ($search) {
            foreach ($search as $key => $value) {
                if ($key == 'value' && $value != "") {
                    
                }
            }
        }

        if ($order) {
            foreach ($order as $k => $ord) {
                $data = $data->orderBy($columns[$ord['column']]['name'], $ord['dir']);
            }
        }

        $recordsTotal = $data->count();
        $recordsFiltered = $data->count();
        $data = $data->skip($start)->take($length)->get();

        return (object) [
            'data'              => $data,
            'recordsTotal'      => $recordsTotal,
            'recordsFiltered'   => $recordsFiltered,
        ];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function saveOne(array $data)
    {
        foreach ($data as $key => $value) {
            $this->model->$key = $value;
        }
        return $this->model->save();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOne($id, $columns = array('*'))
    {
        return $this->model->find($id, $columns);
    }

    /**
     * @param string $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findOneBy(string $field, $value, $columns = array('*'))
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * @param string @field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy(string $field, $value, $columns = array('*'))
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * @param $where
     * @param array $columns
     * @param bool $or
     * @return mixed
     */
    public function findWhere($where, $columns = array('*'), $or = false)
    {
        $result = $this->model;

        foreach ($where as $key => $value) {
            if ($value instanceof \Closure) {
                $result = (!$or) ? $result->where($value) : $result->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $result = (!$or) ? $result->where($field, $operator, $search) : $result->orWhere($field, $operator, $search);
                } elseif (count($value) == 2) {
                    list($field, $search) = $value;
                    $result = (!$or) ? $result->where($field, $search) : $result->orWhere($field, $search);
                }
            } else {
                $result = (!$or) ? $result->where($key, $value) : $result->orWhere($key, $value);
            }
        }
        return $result->get($columns);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $field
     * @return mixed
     */
    public function updateOne(array $data, $id, $field = 'id')
    {
        $result = $this->model->findOrFail($id);
        foreach ($data as $key => $value) {
            $result->$key = $value;
        }
        return $result->save();
    }

    /**
     * @param string $field
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function updateBy(string $field, $value, array $data)
    {
        return $this->model->where($field, $value)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function forceDelete($id)
    {
        return $this->model->find($id)->forceDelete();
    }

    /**
     * @param string $field
     * @param $value
     * @return mixed
     */
    public function deleteBy(string $field, $value)
    {
        return $this->model->where($field, $value)->delete();
    }

    /**
     * @param string $field
     * @param $value
     * @return mixed
     */
    public function forceDeleteBy(string $field, $value)
    {
        return $this->model->where($field, $value)->forceDelete();
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

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria)
                $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }
}