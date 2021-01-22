<?php

namespace App\Validator;

/*
 * Default request validator
 * Used as a fallback when factory does not match a specific validator
 */
class EmptyValidator extends AbstractRequestValidator
{
    /**
     * {@inheritDoc}
     */
    public function checkRequest(): void
    {
        // nothing to do
    }
}
