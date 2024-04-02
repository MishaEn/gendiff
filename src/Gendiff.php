<?php

namespace Hexlet\Code\Gendiff;

use function Hexlet\Code\Parsers\parse;
use function Hexlet\Code\Formatters\format;
use function Hexlet\Code\Formatters\convertToString;

function generate(string $firstFile, string $secondFile, string $format): string
{
    $firstArray = parse($firstFile);
    $secondArray = parse($secondFile);

    $resultArray = findingDifference($firstArray, $secondArray);

    return format($resultArray, $format);
}

function findingDifference(array $firstArray, array $secondArray): array
{
    $resultArray = [];

    $intersecting = array_intersect_key($firstArray, $secondArray);
    $merged = array_merge($firstArray, $secondArray);

    ksort($merged);

    foreach ($merged as $key => $value) {
        if (isset($intersecting[$key])) {
            if ($firstArray[$key] !== $secondArray[$key] && !is_array($value)) {
                $resultArray[] = commitUpdate(convertToString($firstArray[$key]),  convertToString($secondArray[$key]), null, $key);

                continue;
            }

            $resultArray[] = !is_array($value) ?
                commitNothing(convertToString($secondArray[$key]), $key) :
                commitUpdate(null, null, findingDifference($firstArray[$key], $secondArray[$key]), $key);

            continue;
        }

        $resultArray[] = commitWithoutCrossing($firstArray, $secondArray, $key);
    }

    return $resultArray;
}

function commitWithoutCrossing(array $firstArray, array $secondArray, string $key): array
{
    $resultArray = [];

    if (!isset($firstArray[$key]) && isset($secondArray[$key])) {
        return commitAddition(convertToString($secondArray[$key]), $key);
    }

    if (isset($firstArray[$key]) && !isset($secondArray[$key])) {
        return commitDeletion(convertToString($firstArray[$key]), $key);
    }

    if ((isset($firstArray[$key]) && isset($secondArray[$key])) && ($firstArray[$key] === $secondArray[$key])) {
        return commitNothing(convertToString($secondArray[$key]), $key);
    }

    return $resultArray;
}

function commitNothing(mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'nothing', 'from' => null, 'to' => null, 'value' => $value];
}
function commitUpdate(mixed $from, mixed $to, mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'update', 'from' => $from, 'to' => $to, 'value' => $value];
}
function commitAddition(mixed $value, $key): array
{
    return ['key' => $key, 'action' => 'added', 'from' => null, 'to' => null, 'value' => $value];
}

function commitDeletion(mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'removed', 'from' => null, 'to' => null, 'value' => $value];
}