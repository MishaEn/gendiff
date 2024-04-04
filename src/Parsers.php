<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

const YAML_EXTENSION_LIST = ['yml', 'yaml'];

function parse(string $path): array
{
    $fileExtension = getFileExtension($path);
    /** @phpstan-ignore-next-line */
    if (in_array($fileExtension, YAML_EXTENSION_LIST)) {
        return yamlToArray($path);
    }

    if ($fileExtension === 'json') {
        return jsonToArray($path);
    }

    return [];
}

function yamlToArray(string $path): array
{
    return Yaml::parseFile($path);
}

function jsonToArray(string $path): array
{
    /** @phpstan-ignore-next-line */
    return json_decode(file_get_contents($path), true);
}

function getFileExtension(string $path): string
{
    /** @phpstan-ignore-next-line */
    return substr(strrchr($path, '.'), 1);
}
