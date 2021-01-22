<?php

namespace App\Validator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/*
 * User Activation Validator
 * Ensure that minimum parameters exists for processing a user activation
 */
class ActivateValidator extends AbstractRequestValidator
{
    /**
     * {@inheritDoc}
     */
    public function checkRequest(): void
    {
        // Request should contains an activation code
        $code = $this->request->attributes->get('activationCode');
        if (empty($code)) {
            throw new BadRequestHttpException('Missing activation code');
        }

        // Body should be empty
        $rawContent = $this->request->getContent();
        if (!empty($rawContent)) {
            throw new BadRequestHttpException('Invalid body content');
        }
    }
}
