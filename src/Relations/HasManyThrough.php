<?php

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Relations\HasManyThrough as BaseHasManyThrough;
use Illuminate\Support\Collection;
use stdClass;
use Volosyuk\SimpleEloquent\ModelAccessor;

/**
 * Class HasManyThroughWithSimple
 * @package Volosyuk\SimpleEloquent
 */
class HasManyThrough extends BaseHasManyThrough
{
    use Relation, Pivot;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  Collection  $results
     * @param  string  $relation
     * @return array|stdClass[]
     */
    protected function matchSimple(array &$models, Collection $results, $relation)
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[ModelAccessor::get($result, 'laravel_through_key')][] = $result;
        }

        foreach ($models as &$model) {
            $value = [];

            if (isset($dictionary[$key = ModelAccessor::get($model, $this->parent->getKeyName())])) {
                $value = $dictionary[$key];
            }

            ModelAccessor::set($model, $relation, Collection::make($value));
        }
        unset($model);

        return $models;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraintsSimple(array $models)
    {
        $table = $this->parent->getTable();

        $this->query->whereIn($table.'.'.$this->firstKey, $this->getKeys($models, $this->localKey));
    }
}
