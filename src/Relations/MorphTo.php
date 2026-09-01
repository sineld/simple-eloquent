<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo as BaseMorphTo;
use Illuminate\Support\Collection;
use Volosyuk\SimpleEloquent\Builder;
use Volosyuk\SimpleEloquent\ModelAccessor;

/**
 * Class MorphToWithSimple
 */
class MorphTo extends BaseMorphTo
{
    use Relation;

    /**
     * Get the results of the relationship.
     *
     * Called via eager load method of Eloquent query builder.
     *
     * @return mixed
     */
    protected function getEagerSimple()
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchSimpleToMorphParents($type, $this->getSimpleResultsByType($type));
        }

        return $this->models;
    }

    /**
     * @return mixed
     */
    public function eagerLoadAndMatchSimple($models, $name)
    {
        $this->addEagerConstraintsSimple($models);

        return $this->getEagerSimple();
    }

    /**
     * Get all of the relation results for a type.
     *
     * @param  string  $type
     * @return Collection
     */
    protected function getSimpleResultsByType($type)
    {
        /**
         * @var Model $instance
         */
        $instance = $this->createModelByType($type);

        /**
         * @var Builder $query
         */
        $query = $this->replayMacros($instance->newQuery())
            ->mergeConstraintsFrom($this->getQuery())
            ->with($this->getQuery()->getEagerLoads());

        return $query->whereIn(
            $instance->getTable().'.'.$instance->getKeyName(), $this->gatherKeysByType($type, $instance->getKeyType())
        )->getSimple();
    }

    /**
     * Match the results for a given type to their parents.
     *
     * @param  string  $type
     * @return void
     */
    protected function matchSimpleToMorphParents($type, Collection $results)
    {
        foreach ($results as $result) {
            foreach ($this->models as &$model) {
                if (
                    ModelAccessor::get($model, $this->morphType) === $type
                    &&
                    /*
                     * Key columns can surface as int or string depending on the
                     * PDO driver, so compare them as strings like Eloquent does.
                     */
                    (string) ModelAccessor::get($model, $this->foreignKey) === (string) ModelAccessor::get($result, $this->parent->getKeyName())
                ) {
                    ModelAccessor::set($model, $this->relationName, $result);
                }
            }
            unset($model);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @return void
     */
    public function addEagerConstraintsSimple(array $models)
    {
        $this->buildDictionarySimple($this->models = $models);
    }

    /**
     * Build a dictionary with the models.
     *
     * @return void
     */
    protected function buildDictionarySimple(array $models)
    {
        foreach ($models as $model) {
            if (ModelAccessor::get($model, $this->morphType)) {
                $this->dictionary[ModelAccessor::get($model, $this->morphType)][ModelAccessor::get($model, $this->foreignKey)][] = $model;
            }
        }
    }
}
