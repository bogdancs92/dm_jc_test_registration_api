<?php

namespace App\Tests\Security;

use App\Security\PasswordEncoder;
use PHPUnit\Framework\TestCase;
use TypeError;

/*
 * Test suite for Password Encoder
 */
class PasswordEncoderTest extends TestCase
{
    /**
     * @var PasswordEncoder
     */
    protected $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PasswordEncoder();
    }

    public function testFuncExists()
    {
        $this->assertTrue(function_exists('password_hash'));
    }

    public function testPasswordLength()
    {
        // Default (Bcrypt generate 60 char password)
        $this->assertEquals(60, strlen($this->encoder->encodePassword('foo')));
    }

    public function testHash()
    {
        $hash = $this->encoder->encodePassword('foo');
        $this->assertEquals($hash, crypt('foo', $hash));
    }

    public function testIsPasswordValid()
    {
        $hash = $this->encoder->encodePassword('foo');
        $this->assertTrue($this->encoder->isPasswordValid($hash, 'foo'));
    }

    public function testIsPasswordEmpty()
    {
        $hash = $this->encoder->encodePassword('');
        $this->assertTrue($this->encoder->isPasswordValid($hash, ''));
    }

    public function testIsPasswordMissing()
    {
        $this->expectException(TypeError::class);
        $this->encoder->encodePassword();
    }

    public function testIsPasswordInvalid()
    {
        $this->expectException(TypeError::class);
        $this->encoder->encodePassword([]);
    }
}
