<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/*
 * Test suite for ApiError Entity.
 */
class ApiErrorTest extends TestCase
{
    protected $entity;

    protected function setUp(): void
    {
        $this->entity = $this->getMockForAbstractClass('App\Entity\ApiError');
    }

    public function testProperties()
    {
        $this->assertNull($this->entity->getCode());

        $this->entity->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $this->entity->getCode());

        $this->entity->setMessage('error message');
        $this->assertSame('error message', $this->entity->getMessage());
    }

    public function testSerialization()
    {
        $this->entity->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->entity->setMessage('error message');

        $array = $this->entity->toArray();
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $array['error']['code']);
        $this->assertSame('error message', $array['error']['message']);
    }

    public function testUnSerialization()
    {
        $array = ['error' => ['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => 'error message']];
        $this->entity->fromArray($array);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $this->entity->getCode());
        $this->assertSame('error message', $this->entity->getMessage());
    }
}
