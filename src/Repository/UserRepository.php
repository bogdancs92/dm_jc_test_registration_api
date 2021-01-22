<?php

namespace App\Repository;

use App\Entity\BaseEntity;
use App\Entity\User;
use Doctrine\DBAL\Driver\Connection;
use Exception;
use Psr\Log\LoggerInterface;

/*
 * User Repository
 * Service used for all interractions with DBMS
 */
class UserRepository implements RepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * Get all users.
     *
     * @throws Exception
     */
    public function findAll(): array
    {
        $users = [];
        $query = 'SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at ';
        $query .= 'FROM users ';
        $stmt = $this->connection->prepare($query);

        try {
            $stmt->execute();
            $dbUsers = $stmt->fetchAllAssociative();
            foreach ($dbUsers as $dbData) {
                $users[] = $this->loadEntity($dbData);
            }
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when getting all entities from users [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;
        }

        return $users;
    }

    /**
     * Get a user by it's ID.
     *
     * @throws Exception
     */
    public function find(int $id): ?User
    {
        $query = 'SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at ';
        $query .= 'FROM users ';
        $query .= 'WHERE id = :id ';
        $stmt = $this->connection->prepare($query);
        try {
            $stmt->execute(['id' => $id]);
            $dbData = $stmt->fetchAssociative();
            if (false === $dbData) {
                return null;
            }

            return $this->loadEntity($dbData);
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when getting one entity from users [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;

            return null;
        }

        return null;
    }

    /**
     * Find any user matching search criteria.
     *
     * @throws Exception
     */
    public function findBy(array $critera): array
    {
        $users = [];
        $query = 'SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at ';
        $query .= 'FROM users ';
        $query .= 'WHERE 1=1 ';
        foreach ($critera as $key => $value) {
            $query .= "AND $key = :$key ";
        }

        $stmt = $this->connection->prepare($query);
        try {
            $stmt->execute($critera);
            $dbUsers = $stmt->fetchAllAssociative();
            foreach ($dbUsers as $dbData) {
                $users[] = $this->loadEntity($dbData);
            }
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when getting all entities from users with criteria [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;
        }

        return $users;
    }

    /**
     * Get first user matching search criteria.
     *
     * @throws Exception
     */
    public function findOneBy(array $critera): ?User
    {
        $query = 'SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at ';
        $query .= 'FROM users ';
        $query .= 'WHERE 1=1 ';
        foreach ($critera as $key => $value) {
            $query .= "AND $key = :$key ";
        }
        $stmt = $this->connection->prepare($query);
        try {
            $stmt->execute($critera);
            $dbData = $stmt->fetchAssociative();
            if (false === $dbData) {
                return null;
            }

            return $this->loadEntity($dbData);
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when getting one entity from users with criteria [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;
        }

        return null;
    }

    /**
     * Persist entity into DB.
     *
     * @param User $entity
     *
     * @throws Exception
     */
    public function persist(BaseEntity $entity): void
    {
        if ($entity->getId()) {
            $this->persistUpdate($entity);
        } else {
            $this->persistInsert($entity);
        }
    }

    /**
     * Update entity User into DB.
     *
     * @throws Exception
     */
    protected function persistUpdate(User $user)
    {
        $dbData = $user->toArray();
        $keys = array_keys($dbData);

        $query = '';
        $query .= 'UPDATE users SET ';
        foreach ($keys as $key => $keyName) {
            if ('id' === $keyName) {
                continue;
            }
            $query .= "$keyName = :$keyName ";
            if ($key != array_key_last($keys)) {
                $query .= ', ';
            }
        }
        $query .= 'WHERE id = :id ';
        try {
            $stmt = $this->connection->prepare($query);
            foreach ($dbData as $key => &$value) {
                if (is_bool($value)) {
                    $value = ($value ? 'TRUE' : 'FALSE');
                }
            }
            $stmt->execute($dbData);
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when updating one entity into users [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;
        }
    }

    /**
     * Insert entity User into DB.
     *
     * @throws Exception
     */
    protected function persistInsert(User $user)
    {
        $dbData = $user->toArray();
        unset($dbData['id']);
        $keys = array_keys($dbData);

        $query = 'INSERT INTO users ('.implode(', ', $keys).') VALUES (:'.implode(', :', $keys).' )';
        try {
            $stmt = $this->connection->prepare($query);
            foreach ($dbData as $key => &$value) {
                if (is_bool($value)) {
                    $value = ($value ? 'TRUE' : 'FALSE');
                }
            }
            $stmt->execute($dbData);

            // Set inserted ID into entity
            $user->setId(intval($this->connection->lastInsertId()));
        } catch (Exception $e) {
            $this->logger->error(sprintf('%2$s error when inserting one entity into users [%1$s ]', $e->__toString(), __METHOD__));
            throw $e;
        }
    }

    /**
     * Load DB data into an entity.
     */
    protected function loadEntity(array $dbData): User
    {
        $user = new User();
        $user->fromArray($dbData);

        return $user;
    }
}
