<?php

namespace App\Entity;

/**
 * Base class used by all entities.
 */
abstract class BaseEntity
{
    /**
     * uniq ID.
     *
     * @var int
     */
    protected $id;

    /**
     * Map Entity to array.
     * Without context, whole entity is returned.
     *
     * @param int $context Serialization context to use
     */
    abstract public function toArray(int $context = null): array;

    /**
     * Load Entity from array.
     */
    abstract public function fromArray(array $array): BaseEntity;

    /**
     * Get uniq ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uniq ID.
     *
     * @param int $id uniq ID
     *
     * @return self
     */
    public function setId(int $id): BaseEntity
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Serialize Entity to JSON.
     * Without context, whole entity is serialized.
     *
     * @param int $context Serialization context to use
     */
    public function toJson(int $context = null): string
    {
        return json_encode($this->toArray(), $context);
    }

    /**
     * Unserialize JSON Entity.
     */
    public function fromJson(string $json): BaseEntity
    {
        return $this->fromArray(json_decode($json, true));
    }
}
