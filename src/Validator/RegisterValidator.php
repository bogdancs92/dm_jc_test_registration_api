<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/*
 * User Registration Validator
 * Ensure that minimum parameters exists for processing a new registration
 */
class RegisterValidator extends AbstractRequestValidator
{
    /**
     * {@inheritDoc}
     */
    public function checkRequest(): void
    {
        $rawContent = $this->request->getContent();
        $data = json_decode($rawContent, true);
        if (empty($data)) {
            throw new BadRequestHttpException('Invalid body content');
        }

        // Try to build a User
        $user = new User();
        $user->fromArray($data);

        // Check minimum data required
        if (!$user->getEmail()) {
            throw new UnprocessableEntityHttpException('No email provided');
        }
        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new UnprocessableEntityHttpException('Invalid email provided');
        }
        if (!$user->getPassword()) {
            throw new UnprocessableEntityHttpException('No password provided');
        }
    }
}
