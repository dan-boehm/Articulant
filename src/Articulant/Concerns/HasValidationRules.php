<?php

namespace DanBoehm\Articulant\Concerns;


use DanBoehm\Articulant\Model;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * Trait HasRulesValidation
 *
 * @package DanBoehm\Articulant\Concerns
 * @mixin Model
 */
trait HasValidationRules
{
    /**
     * Return the rules to validate this model against.
     *
     * @return array
     */
    abstract protected function getRules() : array;

    /**
     * Returns the data to be validated.
     *
     * @return array
     */
    abstract protected function getValidationData() : array;    // This needs to be here for unit testing.

    /**
     * Returns a validator that will validate the current state of the model.
     *
     * @return Validator
     */
    public function getValidator() : Validator
    {
        return ValidatorFacade::make($this->getValidationData(), $this->getRules());
    }
}
