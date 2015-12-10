<?php namespace Waavi\Translation\Repositories;

class Repository
{
    /**
     *  Return the model related to this finder.
     *
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     *  Check if the model's table exists
     *
     *  @return boolean
     */
    public function tableExists()
    {
        return $this->model->getConnection()->getSchemaBuilder()->hasTable($this->model->getTable());
    }

    /**
     *  Retrieve all records.
     *
     *  @param array $related Related object to include.
     *  @param integer $perPage Number of records to retrieve per page. If zero the whole result set is returned.
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function all($related = [], $perPage = 0)
    {
        $results = $this->model->with($related)->orderBy('created_at', 'DESC');
        return $perPage ? $results->paginate($perPage) : $results->get();
    }

    /**
     *  Retrieve all trashed.
     *
     *  @param array $related Related object to include.
     *  @param integer $perPage Number of records to retrieve per page. If zero the whole result set is returned.
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function trashed($related = [], $perPage = 0)
    {
        $trashed = $this->model->onlyTrashed()->with($related);
        return $perPage ? $trashed->paginate($perPage) : $trashed->get();
    }

    /**
     *  Retrieve a single record by id.
     *
     *  @param integer $id
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, $related = [])
    {
        return $this->model->with($related)->find($id);
    }

    /**
     *  Retrieve a single record by id.
     *
     *  @param integer $id
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function findTrashed($id, $related = [])
    {
        return $this->model->onlyTrashed()->with($related)->find($id);
    }

    /**
     *  Remove a record.
     *
     *  @param  \Illuminate\Database\Eloquent\Model $model
     *  @return boolean
     */
    public function delete($id)
    {
        $model = $this->model->where('id', $id)->first();
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    /**
     *  Restore a record.
     *
     *  @param  int $id
     *  @return boolean
     */
    public function restore($id)
    {
        $model = $this->findTrashed($id);
        if ($model) {
            $model->restore();
        }
        return $model;
    }

    /**
     *  Returns total number of entries in DB.
     *
     *  @return integer
     */
    public function count()
    {
        return $this->model->count();
    }
}
