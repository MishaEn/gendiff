<?php

namespace Hexlet\Code\Tests\GendiffTest;

use PHPUnit\Framework\TestCase;
use function Hexlet\Code\Parsers\parse;

class ParsersTest extends TestCase
{
    public function testParse(): void
    {
        $expected = [
            'host' => 'hexlet.io',
            'timeout' => 50,
            'proxy' => '123.234.53.22',
            'follow' => false
        ];

        $this->assertEquals($expected, parse('file1.json'));
        $this->assertEquals($expected, parse('file1.yml',));
    }
}