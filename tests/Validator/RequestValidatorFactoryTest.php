<?php

namespace App\Tests\Validator;

use App\Validator\EmptyValidator;
use App\Validator\RequestValidatorFactory;
use App\Validator\RequestValidatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/*
 * Test suite for RequestValidatorFactory
 */
class RequestValidatorFactoryTest extends TestCase
{
    public function testValidValidator()
    {
        $request = new Request([], [], ['_route' => 'register'], [], [], [], []);
        $validator = RequestValidatorFactory::createValidator($request);
        $this->assertInstanceOf(RequestValidatorInterface::class, $validator);
        $this->assertNotInstanceOf(EmptyValidator::class, $validator);
    }

    public function testInvalidValidator()
    {
        $request = new Request([], [], ['_route' => 'foo'], [], [], [], []);
        $validator = RequestValidatorFactory::createValidator($request);
        $this->assertInstanceOf(EmptyValidator::class, $validator);
    }
}
