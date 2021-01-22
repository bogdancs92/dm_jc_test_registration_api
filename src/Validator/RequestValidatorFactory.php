<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;

/*
 * Factory for Request Validators
 */
class RequestValidatorFactory
{
    const VALIDATOR_CLASS_SUFFIX = 'Validator';

    /**
     * Build a validator.
     * Use the route used in called controller to find which validator to instanciate.
     */
    public static function createValidator(Request $request): ?RequestValidatorInterface
    {
        // Get called route
        $route = $request->attributes->get('_route');
        // Build Validator class name
        $validatorClass = __NAMESPACE__.'\\'.ucfirst($route).self::VALIDATOR_CLASS_SUFFIX;
        // Instanciate Validator if class is found
        if (class_exists($validatorClass)) {
            return new $validatorClass($request);
        }
        // Class not found. Assume there is no specific Validator for this request,
        // fallback to EmptyValidator
        return new EmptyValidator($request);
    }
}
