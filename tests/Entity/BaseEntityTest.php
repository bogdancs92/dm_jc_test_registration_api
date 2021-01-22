<?php

namespace App\Tests\Entity;

use App\Entity\BaseEntity;
use PHPUnit\Framework\TestCase;

/*
 * Test suite for Base Entity.
 */
class BaseEntityTest extends TestCase
{
    protected $entity;

    protected function setUp(): void
    {
        // Create a new instance from the Abstract Class
        $this->entity = new class() extends BaseEntity {
            private $fakeProperty = 'test';

            public function toArray(int $context = null): array
            {
                return ['id' => 1, 'fake_property' => $this->fakeProperty];
            }

            public function fromArray(array $array): self
            {
                $this->fakeProperty = $array['fake_property'] ?? null;

                return $this;
            }

            public function getProperty(): ?string
            {
                return $this->fakeProperty;
            }
        };
    }

    public function testToArray()
    {
        $this->assertSame(['id' => 1, 'fake_property' => 'test'], $this->entity->toArray());
    }

    public function testFromArray()
    {
        $this->assertInstanceOf(
            BaseEntity::class,
            $this->entity->fromArray([])
        );
        $this->assertNull($this->entity->getProperty());

        $this->assertInstanceOf(
            BaseEntity::class,
            $this->entity->fromArray(['fake_property' => 'foo'])
        );
        $this->assertSame('foo', $this->entity->getProperty());
    }

    public function testToJson()
    {
        $expected = json_encode(['id' => 1, 'fake_property' => 'test']);
        $this->assertSame($expected, $this->entity->toJson());
    }

    public function testFromJson()
    {
        $json = json_encode(['fake_property' => 'foo']);
        $this->assertSame('foo', $this->entity->fromJson($json)->getProperty());
    }
}
