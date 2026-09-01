<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Support\Collection;
use Volosyuk\SimpleEloquent\ModelAccessor;

trait HasOneOrMany
{
    /**
     * Match the eagerly loaded results to their single parents.
     *
     * @param  string  $relation
     * @return array
     */
    protected function matchOneSimple(array $models, Collection $results, $relation)
    {
        return $this->matchOneOrManySimple($models, $results, $relation, 'one');
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  string  $relation
     * @param  string  $type
     * @return array
     */
    protected function matchOneOrManySimple(array &$models, Collection $results, $relation, $type)
    {
        $dictionary = [];

        $foreign = $this->getForeignKeyName();

        foreach ($results as $result) {
            $dictionary[ModelAccessor::get($result, $foreign)][] = $result;
        }

        foreach ($models as &$model) {
            $key = ModelAccessor::get($model, $this->localKey);

            if (isset($dictionary[$key])) {
                $value = $this->getRelationValue($dictionary, $key, $type);

                ModelAccessor::set($model, $relation, $value);
            }
        }
        unset($model);

        return $models;
    }
}
