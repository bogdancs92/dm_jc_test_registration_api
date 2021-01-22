<?php

namespace App\Tests\Validator;

use App\Validator\ActivateValidator;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/*
 * Test suite for Registration Activation Validator
 */
class VerifyValidatorTest extends TestCase
{
    public function testValidatorOK()
    {
        $request = new Request([], [], ['_route' => 'activate', 'activationCode' => 'ABCD']);
        $validator = new ActivateValidator($request);

        $anExceptionWasThrown = false;
        try {
            $validator->checkRequest();
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    public function testValidatorWithMissingActivationCode()
    {
        $request = new Request([], [], ['_route' => 'activate']);
        $validator = new ActivateValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithEmptyActivationCode()
    {
        $request = new Request([], [], ['_route' => 'activate', 'activationCode' => '']);
        $validator = new ActivateValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithNonEmptyBody()
    {
        $request = new Request([], [], ['_route' => 'activate', 'activationCode' => 'ABCD'], [], [], [], 'body');
        $validator = new ActivateValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }
}
