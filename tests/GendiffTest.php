<?php

namespace Hexlet\Code\Tests\GendiffTest;

use PHPUnit\Framework\TestCase;
use function Hexlet\Code\Gendiff\generate;
use function Hexlet\Code\Parsers\parse;

class GendiffTest extends TestCase
{
    public function testGenerate(): void
    {
        $expected = "{\n";
        $expected .= "  - follow: false\n";
        $expected .= "    host: hexlet.io\n";
        $expected .= "  - proxy: 123.234.53.22\n";
        $expected .= "  - timeout: 50\n";
        $expected .= "  + timeout: 20\n";
        $expected .= "  + verbose: true\n";
        $expected .= "}\n";

        $this->assertEquals($expected, generate('file1.json', 'file2.json'));
        $this->assertEquals($expected, generate('file1.yml', 'file3.yml'));
        $this->assertEquals($expected, generate('file1.yml', 'file2.yaml'));
    }
}
