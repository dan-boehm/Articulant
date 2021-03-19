<?php

namespace DanBoehm\Articulant\Concerns;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasValidation
 *
 * @package DanBoehm\Articulant\Concerns
 * @mixin Model
 */
trait HasValidation
{
    /**
     * Returns a validator that will validate the current state of the model.
     *
     * @return Validator
     */
    abstract protected function getValidator() : Validator;

    /**
     * Returns the data to be validated.
     *
     * @return array
     */
    protected function getValidationData() : array
    {
        $hidden = $this->getHidden();
        $visible = $this->getVisible();
        $this->setHidden([])->setVisible([]);

        $data = $this->attributesToArray();

        $this->setHidden($hidden)->setVisible($visible);

        return $data;
    }

    /**
     * Validates the Model
     *
     * @return bool
     */
    public function validate() : bool
    {
        return $this->getValidator()->passes();
    }
}
