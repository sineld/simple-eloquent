<?php

declare(strict_types=1);

namespace Volosyuk\SimpleEloquent;

use Exception;
use stdClass;

/**
 * Perform simple operations with models depends on their type
 *
 * Class ModelAccessor
 */
class ModelAccessor
{
    public static function set(&$model, $attribute, $value)
    {
        if (is_array($model)) {
            $model[$attribute] = $value;
        } elseif (is_object($model)) {
            $model->{$attribute} = $value;
        }
    }

    /**
     * @return mixed|null
     */
    public static function get($model, $attribute)
    {
        if (is_array($model)) {
            return $model[$attribute];
        } elseif (is_object($model)) {
            return $model->{$attribute};
        }

        return null;
    }

    public static function delete(&$model, $attribute)
    {
        if (is_array($model)) {
            unset($model[$attribute]);
        } elseif (is_object($model)) {
            unset($model->{$attribute});
        }
    }

    /**
     * @return bool
     */
    public static function exists($model, $attribute)
    {
        if (is_array($model)) {
            return isset($model[$attribute]);
        } elseif (is_object($model)) {
            return property_exists($model, $attribute);
        }

        return false;
    }

    /**
     * @return array|stdClass
     *
     * @throws Exception
     */
    public static function createBasedOnModel($model)
    {
        if (is_array($model)) {
            return self::create(true);
        } elseif (is_object($model)) {
            return self::create(false);
        }

        throw new Exception('Model type is not valid');
    }

    /**
     * @param  bool  $buildArray
     * @return array|stdClass
     */
    private static function create($buildArray = true)
    {
        if ($buildArray) {
            return [];
        }

        return new stdClass;
    }
}
