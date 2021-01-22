<?php

namespace App\Tests\Validator;

/*
 * Test suite for Registration Validator
 */
use App\Validator\RegisterValidator;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RegisteralidatorTest extends TestCase
{
    public function testValidator()
    {
        $body = [
            'email' => 'valid@domain.com',
            'password' => 'password',
        ];
        $request = new Request([], [], ['_route' => 'register'], [], [], [], json_encode($body));
        $validator = new RegisterValidator($request);

        $anExceptionWasThrown = false;
        try {
            $validator->checkRequest();
        } catch (Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    public function testValidatorWithMissingEmail()
    {
        $body = [
            'password' => 'password',
        ];

        $request = new Request([], [], ['_route' => 'register'], [], [], [], json_encode($body));
        $validator = new RegisterValidator($request);

        $this->expectException(UnprocessableEntityHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithInvalidEmail()
    {
        $body = [
            'email' => 'email',
            'password' => 'password',
        ];

        $request = new Request([], [], ['_route' => 'register'], [], [], [], json_encode($body));
        $validator = new RegisterValidator($request);

        $this->expectException(UnprocessableEntityHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithMissingPassword()
    {
        $body = [
            'email' => 'valid@domain.com',
        ];

        $request = new Request([], [], ['_route' => 'register'], [], [], [], json_encode($body));
        $validator = new RegisterValidator($request);

        $this->expectException(UnprocessableEntityHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithEmptyPassword()
    {
        $body = [
            'email' => 'valid@domain.com',
            'password' => '',
        ];

        $request = new Request([], [], ['_route' => 'register'], [], [], [], json_encode($body));
        $validator = new RegisterValidator($request);

        $this->expectException(UnprocessableEntityHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithNoContent()
    {
        $request = new Request([], [], ['_route' => 'register'], [], [], [], null);
        $validator = new RegisterValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithEmptyContent()
    {
        $request = new Request([], [], ['_route' => 'register'], [], [], [], '');
        $validator = new RegisterValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }

    public function testValidatorWithMalformedJson()
    {
        $request = new Request([], [], ['_route' => 'register'], [], [], [], '{"key": value}');
        $validator = new RegisterValidator($request);

        $this->expectException(BadRequestHttpException::class);
        $validator->checkRequest();
    }
}
