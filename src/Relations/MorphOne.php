<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Relations\MorphOne as BaseMorphOne;
use Illuminate\Support\Collection;

/**
 * Class MorphOneWithSimple
 */
class MorphOne extends BaseMorphOne
{
    use HasOneOrMany, Relation;

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  string  $relation
     * @return array
     */
    protected function matchSimple(array $models, Collection $results, $relation)
    {
        return $this->matchOneSimple($models, $results, $relation);
    }
}
