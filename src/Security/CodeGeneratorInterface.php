<?php

namespace App\Security;

/*
 * Code Generator Interface
 */
interface CodeGeneratorInterface
{
    /**
     * Generate a code.
     */
    public function generateCode(): string;

    /**
     * Compare 2 codes.
     */
    public function compareCodes(string $code1, string $code2): bool;
}
