<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany as BaseMorphToMany;

/**
 * Class MorphToManyWithSimple
 */
class MorphToMany extends BaseMorphToMany
{
    use Pivot, Relation;

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @return void
     */
    public function addEagerConstraintsSimple()
    {
        $this->query->where($this->table.'.'.$this->morphType, $this->morphClass);
    }
}
