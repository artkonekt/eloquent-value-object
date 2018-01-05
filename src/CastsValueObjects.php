<?php
/**
 * Contains the CastsValueObjects trait.
 *
 * @copyright   Copyright (c) 2018 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2018-01-05
 *
 */

namespace Konekt\EloquentValueObject;

trait CastsValueObjects
{

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        if ($this->isValueObjectAttribute($key)) {
            $class = $this->getValueObjectClass($key);

            /**
             * @todo PROBLEMS: 1) multiple fields 2) creation method
             */
            return $class::create($this->getAttributeFromArray($key));
        }

        return parent::getAttributeValue($key);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->isEnumAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if ($this->isValueObjectAttribute($key)) {
            $class = $this->getValueObjectClass($key);
            if (! $value instanceof $class) {
                $value = new $class($value);
            }

            /**
             * @todo Problem: 1) multiple fields 2) scalar value getter
             */
            $this->attributes[$key] = $value->value();

            return $this;
        }

        parent::setAttribute($key, $value);
    }

    /**
     * Returns whether the attribute was marked as value object
     *
     * @param $key
     *
     * @return bool
     */
    private function isValueObjectAttribute($key)
    {
        return isset($this->valueObjects[$key]);
    }

    /**
     * Returns the value object class. Supports 'FQCN\Class@method()' notation
     *
     * @param $key
     *
     * @return mixed
     */
    private function getValueObjectClass($key)
    {
        $result = $this->valueObjects[$key];
        if (strpos($result, '@')) {
            $class  = str_before($result, '@');
            $method = str_after($result, '@');

            // If no namespace was set, prepend the Model's namespace to the
            // class that resolves the real class. Prevent this behavior,
            // by setting the resolver class with a leading backslash
            if (class_basename($class) == $class) {
                $class =
                    str_replace_last(
                        class_basename(get_class($this)),
                        $class,
                        self::class
                    );
            }

            $result = $class::$method();
        }

        return $result;
    }
}
