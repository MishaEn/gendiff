<?php

use PHPUnit\Framework\TestCase;
use function Hexlet\Code\Gendiff\generate;

class GendiffTest extends TestCase
{
    protected function setUp():void
    {
        $this->files = [
            'json1' => 'tests/fixtures/file1.json',
            'json2' => 'tests/fixtures/file2.json',
            'yaml1' => 'tests/fixtures/file2.yaml',
            'yaml2' => 'tests/fixtures/file3.yml'
        ];
    }
    /**
     * @dataProvider filesProvider
     */
    public function testGendiff(string $format, string $expected, string $file1, string $file2): void
    {
        $actual = generate(
            $this->createFilePath($file1),
            $this->createFilePath($file2),
            $format
        );

        $this->assertEquals(file_get_contents($this->createFilePath($expected)), $actual);
    }

    public static function filesProvider(): array
    {
        return [
            'stylish format json' => [
                'format' => 'stylish',
                'expected' => 'result.stylish',
                'file1' => 'file1.json',
                'file2' => 'file2.json'
            ],
            'plain format json' => [
                'format' => 'plain',
                'expected' => 'result.plain',
                'file1' => 'file1.json',
                'file2' => 'file2.json'
            ],
            'plain format yaml' => [
                'format' => 'plain',
                'expected' => 'result.plain',
                'file1' => 'file1.yml',
                'file2' => 'file2.yaml'
            ],
            'json format json' => [
                'format' => 'json',
                'expected' => 'result.json',
                'file1' => 'file1.json',
                'file2' => 'file2.json'
            ],
            'json format yaml' => [
                'format' => 'json',
                'expected' => 'result.json',
                'file1' => 'file1.yml',
                'file2' => 'file2.yaml'
            ],
        ];
    }

    private function createFilePath(string $fileName): string
    {
        return realpath(
            implode(
                '/',
                [
                    __DIR__,
                    'fixtures',
                    $fileName
                ]
            )
        );
    }
}
