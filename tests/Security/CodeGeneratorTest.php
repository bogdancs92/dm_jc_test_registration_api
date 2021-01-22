<?php

namespace App\Tests\Security;

use App\Security\CodeGenerator;
use PHPUnit\Framework\TestCase;
use TypeError;

/*
 * Test suite for Code Generator
 */
class CodeGeneratorTest extends TestCase
{
    /**
     * @var CodeGenerator
     */
    protected $generator;

    protected function setUp(): void
    {
        $this->generator = new CodeGenerator();
    }

    public function testGeneratorRandomCode()
    {
        $code = $this->generator->generateCode();
        $this->assertRegExp("/^\d{".CodeGenerator::NB_DIGITS.'}$/', $code);
    }

    public function testGeneratorLessThan4Chars()
    {
        mt_srand(973); // next call of mt_rand will generate value 8
        $code = $this->generator->generateCode();
        $this->assertSame('0008', $code);
    }

    public function testCompareOK()
    {
        $code = $this->generator->generateCode();

        $res = $this->generator->compareCodes($code, $code);
        $this->assertTrue($res);
    }

    public function testCompareKO()
    {
        $code = $this->generator->generateCode();

        $res = $this->generator->compareCodes($code, 'foo');
        $this->assertFalse($res);
    }

    public function testCompareKOEmpty1()
    {
        $code = $this->generator->generateCode();

        $res = $this->generator->compareCodes('', $code);
        $this->assertFalse($res);
    }

    public function testCompareKOEmpty2()
    {
        $code = $this->generator->generateCode();

        $res = $this->generator->compareCodes($code, '');
        $this->assertFalse($res);
    }

    public function testCompareKOMissing1()
    {
        $this->expectException(TypeError::class);
        $this->generator->compareCodes('code');
    }

    public function testCompareKOMissing2()
    {
        $this->expectException(TypeError::class);
        $this->generator->compareCodes();
    }
}
