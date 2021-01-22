<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Statement;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/*
 * Test suite for User Repository
 */
class UserRepositoryTest extends TestCase
{
    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @var MockObject
     */
    protected $connection;

    /**
     * @var MockObject
     */
    protected $statement;

    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->statement = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new UserRepository($this->connection, $logger);
    }

    public function testFindAll()
    {
        $fakeDb = [
            [
                'id' => 1,
                'email' => 'email_1',
            ],
            [
                'id' => 2,
                'email' => 'email_2',
            ],
        ];
        $this->statement->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn($fakeDb);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $list = $this->repository->findAll();
        $this->assertCount(count($fakeDb), $list);
    }

    public function testFindAllWithException()
    {
        $this->statement->expects($this->once())
            ->method('fetchAllAssociative')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);
        $this->repository->findAll();
    }

    public function testFindExisting()
    {
        $fakeDb = [
            'id' => 1,
            'email' => 'email_1',
        ];
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($fakeDb);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $user = $this->repository->find(1);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindNotExisting()
    {
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $user = $this->repository->find(1);
        $this->assertNull($user);
    }

    public function testFindWithExceptiong()
    {
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);

        $this->repository->find(1);
    }

    public function testFindBy()
    {
        $fakeDb = [
            [
                'id' => 1,
                'email' => 'john@doe.com',
            ],
        ];
        $this->statement->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn($fakeDb);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 AND id = :id AND email = :email ')
            ->willReturn($this->statement);

        $list = $this->repository->findBy(['id' => 1, 'email' => 'john@doe.com']);
        $this->assertCount(count($fakeDb), $list);
    }

    public function testFindByWithException()
    {
        $this->statement->expects($this->once())
            ->method('fetchAllAssociative')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 AND id = :id ')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);

        $this->repository->findBy(['id' => 1]);
    }

    public function testFindOneByExisting()
    {
        $fakeDb = [
            'id' => 1,
            'email' => 'email_1',
        ];
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($fakeDb);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 AND id = :id ')
            ->willReturn($this->statement);

        $user = $this->repository->findOneBy(['id' => 1]);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindOneByWithoutCriteria()
    {
        $fakeDb = [
            'id' => 1,
            'email' => 'email_1',
        ];
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn($fakeDb);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 ')
            ->willReturn($this->statement);

        $user = $this->repository->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testFindOneByNotExisting()
    {
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 ')
            ->willReturn($this->statement);

        $user = $this->repository->findOneBy([]);
        $this->assertNull($user);
    }

    public function testFindOneByWithException()
    {
        $this->statement->expects($this->once())
            ->method('fetchAssociative')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, email, password, activated, registered_at, activation_code, activation_code_expire_at, activated_at FROM users WHERE 1=1 AND email = :email ')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);

        $this->repository->findOneBy(['email' => 'email']);
    }

    public function testPersistUpdate()
    {
        $user = new User();
        $user->setId(1);
        $user->setActivated(true);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('UPDATE users SET email = :email , password = :password , registered_at = :registered_at , activated = :activated , activated_at = :activated_at , activation_code = :activation_code , activation_code_expire_at = :activation_code_expire_at WHERE id = :id ')
            ->willReturn($this->statement);

        $res = $this->repository->persist($user);
        $this->assertNull($res); // No returns
    }

    public function testPersistUpdateWithException()
    {
        $user = new User();
        $user->setId(1);
        $user->setActivated(true);

        $this->statement->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);

        $this->repository->persist($user);
    }

    public function testPersistInsert()
    {
        $user = new User();
        $user->setEmail('email');
        $user->setActivated(true);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO users (email, password, registered_at, activated, activated_at, activation_code, activation_code_expire_at) VALUES (:email, :password, :registered_at, :activated, :activated_at, :activation_code, :activation_code_expire_at )')
            ->willReturn($this->statement);

        $this->connection->expects($this->once())
            ->method('lastInsertId')
            ->willReturn(1);

        $res = $this->repository->persist($user);
        $this->assertSame(1, $user->getId());
        $this->assertNull($res); // No returns
    }

    public function testPersistInsertWithException()
    {
        $user = new User();
        $user->setEmail('email');

        $this->statement->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception(''));

        $this->connection->expects($this->once())
            ->method('prepare')
            ->willReturn($this->statement);

        $this->expectException(Exception::class);

        $this->repository->persist($user);
    }
}
