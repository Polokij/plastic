<?php
/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 13.08.18
 * Time: 17:17
 */

namespace Sleimanx2\Plastic;

use function GuzzleHttp\Psr7\parse_request;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EsModel -  provide the behavior of Eloquent ORM for ElasticSearch
 *
 * @package Sleimanx2\Plastic
 */
class EsModel extends Model
{
    use Searchable;

    /**
     * Primary key auto-generated by ElasticSearch
     *
     * @var string $primaryKey
     */
    protected $primaryKey = '_id';

    /** @var string $dateFormat */
    protected $dateFormat = "Y-m-d\TH:i:sP";

    public $syncDocument = false;

    public $returnRowsOnly = true;

    /**
     * Overriding save method
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        //        $query = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists && isset($this->{$this->primaryKey})) {
            $saved = $this->isDirty() ?
                $this->performUpdateElasticSearch() : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsertElasticSearch();
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdateElasticSearch()
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {

            /** @var array $attributes */
            $attributes = $this->getAttributes();

            /** @var string $_id */
            $_id = $attributes[$this->primaryKey];

            // Excluding the primary key from fields - because it is metadata
            if(isset($attributes[$this->primaryKey])){
                unset($attributes[$this->primaryKey]);
            }

            $params = [
                'index' => $this->getDocumentIndex(),
                'type'  => $this->getDocumentType(),
                'id'    => $_id,
                'body'  => [
                    'doc' => $attributes,
                ]
            ];

            $this->original = \Plastic::updateStatement($params);

            $this->fireModelEvent('updated', false);

            $this->syncChanges();
        }

        return true;
    }

    /**
     * Perform a model insert operation.
     *
     * @return bool
     */
    protected function performInsertElasticSearch()
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->attributes;

        $params = [
            'index' => $this->getDocumentIndex(),
            'type'  => $this->getDocumentType(),
            'body'  => $attributes,
        ];
        // Storing the document to Elasticsearch DB
        $this->original = \Plastic::indexStatement($params);
        $this->{$this->primaryKey} = $this->getKeyForSaveQuery();

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method == 'suggest') {
            //Start an elastic dsl suggest query builder
            return \Plastic::suggest()->index($this->getDocumentIndex());
        }
        //Start an elastic dsl search query builder
        return \Plastic::search()->model($this)->$method(...$parameters);

    }

}