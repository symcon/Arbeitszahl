<?php

declare(strict_types=1);
include_once __DIR__ . '/stubs/Validator.php';
class JahresarbeitszahlValidationTest extends TestCaseSymconValidation
{
    public function testValidateJahresarbeitszahl(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }
    public function testValidateJahresarbeitszahlModule(): void
    {
        $this->validateModule(__DIR__ . '/../Jahresarbeitszahl');
    }
}