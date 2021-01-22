<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestValidator base class.
 */
abstract class AbstractRequestValidator implements RequestValidatorInterface
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Check if received request is valid.
     */
    abstract public function checkRequest(): void;
}
