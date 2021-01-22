<?php

namespace App\Tests\Validator;

use App\Validator\EmptyValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/*
 * Test suite for Empty Validator
 */
class EmptyValidatorTest extends TestCase
{
    public function testValidator()
    {
        $request = new Request([], [], ['_route' => 'foo'], [], [], [], []);
        $validator = new EmptyValidator($request);
        $this->assertNull($validator->checkRequest());
    }
}
