<?php

namespace App\Repository;

use App\Entity\BaseEntity;

/*
 * Repository Interface
 */
interface RepositoryInterface
{
    /**
     * Get all entities.
     */
    public function findAll(): array;

    /**
     * Get an entity by it's ID.
     */
    public function find(int $id): ?BaseEntity;

    /**
     * Find any entity matching search criteria.
     */
    public function findBy(array $critera): array;

    /**
     * Get first entity matching search criteria.
     */
    public function findOneBy(array $critera): ?BaseEntity;

    /**
     * Persist entity into DB.
     */
    public function persist(BaseEntity $entity): void;
}
