<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as BaseBelongsToMany;

/**
 * Class BelongsToManyWithSimple
 */
class BelongsToMany extends BaseBelongsToMany
{
    use Pivot, Relation;

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @return void
     */
    public function addEagerConstraintsSimple(array $models)
    {
        $this->query->whereIn($this->getQualifiedForeignPivotKeyName(), $this->getKeys($models, $this->parentKey));
    }
}
