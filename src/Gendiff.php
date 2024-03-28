<?php

namespace Hexlet\Code\Gendiff;

function generate(string $firstFile, string $secondFile): string
{
    $resultArray = [];

    $firstArray = jsonToArray($firstFile);
    $secondArray = jsonToArray($secondFile);

    findingDifference($resultArray, $firstArray, $secondArray);

    return buildResult($resultArray);
}

function findingDifference(array &$resultArray, array $firstArray, array $secondArray): void
{
    $intersecting = array_intersect_key($firstArray, $secondArray);
    $merged = array_merge($firstArray, $secondArray);

    ksort($merged);

    foreach ($merged as $key => $value) {
        if (isset($intersecting[$key])) {
            crossChange($resultArray, $firstArray, $secondArray, $key);
            nothingChanged($resultArray, $firstArray, $secondArray, $key);
        }

        differenceAdding($resultArray, $firstArray, $secondArray, $key);
        differenceRemove($resultArray, $firstArray, $secondArray, $key);
    }
}

function differenceAdding(array &$resultArray, array $firstArray, array $secondArray, string $key): void
{
    if (!isset($firstArray[$key]) && isset($secondArray[$key])) {
        $resultArray[] = sprintf('+ %s: %s', $key, convertToString($secondArray[$key]));
    }
}

function differenceRemove(array &$resultArray, array $firstArray, array $secondArray, string $key): void
{
    if (isset($firstArray[$key]) && !isset($secondArray[$key])) {
        $resultArray[] = sprintf('- %s: %s', $key, convertToString($firstArray[$key]));
    }
}

function nothingChanged(array &$resultArray, array $firstArray, array $secondArray, string $key): void
{
    if ($firstArray[$key] === $secondArray[$key]) {
        $resultArray[] = sprintf('  %s: %s', $key, convertToString($firstArray[$key]));
    }
}

function crossChange(array &$resultArray, array $firstArray, array $secondArray, string $key): void
{
    if ($firstArray[$key] !== $secondArray[$key]) {
        $resultArray[] = sprintf('- %s: %s', $key, convertToString($firstArray[$key]));
        $resultArray[] = sprintf('+ %s: %s', $key, convertToString($secondArray[$key]));
    }
}

function buildResult(array $resultArray): string
{
    $result = '';
    foreach ($resultArray as $value) {
        $result .= sprintf("  %s\n", $value);
    }

    return sprintf("{\n%s}\n", $result);
}

function jsonToArray(string $path): array
{
    $json = file_get_contents($path);

    return json_decode($json, true);
}

function convertToString(mixed $value): string
{
    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }

    return (string) $value;
}
