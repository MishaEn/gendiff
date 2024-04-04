<?php

namespace Differ\Differ;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;
use function Differ\Formatters\convertArray;

function genDiff(string $firstFile, string $secondFile, string $format = 'stylish'): string
{
    $firstArray = convertArray(parse($firstFile));
    $secondArray = convertArray(parse($secondFile));

    $resultArray = findingDifference($firstArray, $secondArray);

    return format($resultArray, $format, $firstArray, $secondArray);
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
                $resultArray[] = commitUpdate($firstArray[$key],  $secondArray[$key], null, $key);

                continue;
            }

            $resultArray[] = !is_array($value) ?
                commitNothing($secondArray[$key], $key) :
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
        return commitAddition($secondArray[$key], $key);
    }

    if (isset($firstArray[$key]) && !isset($secondArray[$key])) {
        return commitDeletion($firstArray[$key], $key);
    }

    if ((isset($firstArray[$key]) && isset($secondArray[$key])) && ($firstArray[$key] === $secondArray[$key])) {
        return commitNothing($secondArray[$key], $key);
    }

    return $resultArray;
}

function commitNothing(mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'nothing', 'from' => null, 'to' => null, 'value' => $value];
}
function commitUpdate(mixed $from, mixed $to, mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'updated', 'from' => $from, 'to' => $to, 'value' => $value];
}
function commitAddition(mixed $value, $key): array
{
    return ['key' => $key, 'action' => 'added', 'from' => null, 'to' => null, 'value' => $value];
}

function commitDeletion(mixed $value, string $key): array
{
    return ['key' => $key, 'action' => 'removed', 'from' => null, 'to' => null, 'value' => $value];
}