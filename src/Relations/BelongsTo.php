<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsTo as BaseBelongsTo;
use Illuminate\Support\Collection;
use Volosyuk\SimpleEloquent\ModelAccessor;

/**
 * Class BelongsToWithSimple
 */
class BelongsTo extends BaseBelongsTo
{
    use Relation;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  string  $relation
     * @return array
     */
    protected function matchSimple(array $models, Collection $results, $relation)
    {
        $foreign = $this->foreignKey;

        $other = $this->ownerKey;

        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[ModelAccessor::get($result, $other)] = $result;
        }

        foreach ($models as &$model) {
            if (isset($dictionary[ModelAccessor::get($model, $foreign)])) {
                ModelAccessor::set($model, $relation, $dictionary[ModelAccessor::get($model, $foreign)]);
            }
        }
        unset($model);

        return $models;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @return void
     */
    public function addEagerConstraintsSimple(array $models)
    {
        $key = $this->related->getTable().'.'.$this->ownerKey;

        $this->query->whereIn($key, $this->getEagerModelKeysSimple($models));
    }

    /**
     * Gather the keys from an array of related models.
     *
     * @return array
     */
    protected function getEagerModelKeysSimple(array $models)
    {
        $keys = [];

        foreach ($models as $model) {
            if (! is_null($value = ModelAccessor::get($model, $this->foreignKey))) {
                $keys[] = $value;
            }
        }

        if (count($keys) === 0) {
            return [$this->related->getIncrementing() &&
            $this->related->getKeyType() === 'int' ? 0 : null, ];
        }

        return array_values(array_unique($keys));
    }
}
