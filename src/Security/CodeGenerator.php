<?php

namespace App\Security;

/*
 * Code Generator : Generate a 4 digit random code
 */
class CodeGenerator implements CodeGeneratorInterface
{
    const NB_DIGITS = 4;
    const CODE_LIFETIME = 60; // 1 mn

    /**
     * Generate a new code.
     */
    public function generateCode(): string
    {
        return str_pad(mt_rand(0, pow(10, self::NB_DIGITS) - 1), self::NB_DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Compare 2 codes.
     */
    public function compareCodes(string $code1, string $code2): bool
    {
        return $code1 === $code2;
    }
}
