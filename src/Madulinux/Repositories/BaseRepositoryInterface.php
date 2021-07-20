<?php
namespace Madulinux\Repositories;

/**
 * Interface BaseRepositoryInterface
 * @package Madulinux\Repositories
 */
interface BaseRepositoryInterface
{
    /**
     * @param array $columns
     * @param $perPage
     * @param $currentPage
     * @return mixed
     */
    public function all($columns = array('*'), $perPage = null, $currentPage = null);

    /**
     * jquery datatable default
     * @param array $columns
     * @param int $start
     * @param int $length
     * @param array $search
     * @param array $order
     * @param array $columnsDef
     * @return mixed
     */
    public function datatable(array $columns, int $start = 0, int $length = 10, array $search, array $order, array $columnsDef = ['*']);

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function saveOne(array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function findOne($id, $columns = array('*'));

    /**
     * @param string $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findOneBy(string $field, $value, $columns = array('*'));

    /**
     * @param string @field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findAllBy(string $field, $value, $columns = array('*'));

    /**
     * @param $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere($where, $columns = array('*'));

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function updateOne(array $data, $id);

    /**
     * @param string $field
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function updateBy(string $field, $value, array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $id
     * @return mixed
     */
    public function forceDelete($id);

    /**
     * @param string $field
     * @param $value
     * @return mixed
     */
    public function deleteBy(string $field, $value);

    /**
     * @param string $field
     * @param $value
     * @return mixed
     */
    public function forceDeleteBy(string $field, $value);
}