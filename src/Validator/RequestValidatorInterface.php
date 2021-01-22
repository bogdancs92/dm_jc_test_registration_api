<?php

namespace App\Validator;

/*
 * Interface for Request Validators.
 */
interface RequestValidatorInterface
{
    /**
     * Check if received request is valid.
     */
    public function checkRequest(): void;
}
