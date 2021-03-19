<?php

namespace DanBoehm\Articulant\Concerns;


use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * Trait HasDefaults
 *
 * Allows ability to set `protected $defaults` to define default attributes for a model.
 *
 * Note that the defaults array literally becomes the model's attributes array upon
 * construction. This means that casting/mutation will not have an opportunity to occur.
 * Pre-stringify any json in your defaults.
 *
 * @package DanBoehm\Articulant\Concerns
 * @mixin EloquentModel
 */
trait HasDefaults
{

    /**
     * Initialize the model for this trait.
     */
    public function initializeHasDefaults() : void
    {
        $this->resetToDefault();
    }

    /**
     * Return the default attributes.
     *
     * @return array
     */
    public function getDefaultAttributes() : array
    {
        $defaults = $this->defaults ?? [];
        foreach($defaults as $key => $value) {
            if (is_array($value) || is_object($value) || $value instanceof \JsonSerializable) {
                $jsonValue = json_encode($value, JSON_THROW_ON_ERROR);
                if ($jsonValue !== false) {
                    $defaults[$key] = $jsonValue;
                }
            }
        }

        return $defaults;
    }

    /**
     * Reset this model to its default attributes.
     *
     * If $preserveExtra is true, attributes that don't have a default value will remain.
     *
     * @param bool $preserveExtra
     *
     * @return $this
     */
    public function resetToDefault($preserveExtra = false) : self
    {
        $defaults = $this->getDefaultAttributes();

        return $this->setRawAttributes($preserveExtra ? array_merge($this->attributes, $defaults) : $defaults);
    }

    /**
     * Returns true if the model has all of the default attribute values.
     *
     * @param bool $ignoreExtra
     *
     * @return bool
     */
    public function isDefault(bool $ignoreExtra = true) : bool
    {
        $defaults = $this->getDefaultAttributes();
        foreach($this->attributes as $attr => $value) {
            if (!array_key_exists($attr, $defaults)) {
                if ($ignoreExtra) {
                    continue;
                }

                return false;
            }

            /** @noinspection TypeUnsafeComparisonInspection */
            if ($value != $defaults[$attr]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $attr
     *
     * @return mixed|null
     * @throws \JsonException
     */
    public function getDefault(string $attr)
    {
        return $this->getDefaultAttributes()[$attr] ?? null;
    }
}
