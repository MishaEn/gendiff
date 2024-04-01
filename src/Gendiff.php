<?php

namespace Hexlet\Code\Gendiff;

use mysql_xdevapi\Result;
use function Hexlet\Code\Parsers\parse;
use function Hexlet\Code\Formatters\format;

const PARSE_FORMAT = [
    'stylish' => 'formatStylish',
    'plain' => 'formatPlain',
];

function generate(string $firstFile, string $secondFile, string $format): string
{
    $firstArray = parse($firstFile);
    $secondArray = parse($secondFile);

    $resultArray = findingDifference($firstArray, $secondArray);

    return buildResult($resultArray, $format);
}

function findingDifference(array $firstArray, array $secondArray): array
{
    $resultArray = [];

    $intersecting = array_intersect_key($firstArray, $secondArray);
    $merged = array_merge($firstArray, $secondArray);

    ksort($merged);

    foreach ($merged as $key => $value) {
        if (isset($intersecting[$key]) && !is_array($value)) {
            if ($firstArray[$key] !== $secondArray[$key]) {
                $resultArray[] = commitUpdate(convertToString($firstArray[$key]),  convertToString($secondArray[$key]), null, $key);
            }
            if ($firstArray[$key] === $secondArray[$key]) {
                $resultArray[] = commitNothing(convertToString($secondArray[$key]), $key);
            }
        }
        if (!isset($intersecting[$key]) && !is_array($value)) {
            $resultArray[] = commitWithoutCrossing($firstArray, $secondArray, $key);
        }
        if (isset($intersecting[$key]) && is_array($value)) {
            $resultArray[] = commitUpdate(null, null, findingDifference($firstArray[$key], $secondArray[$key]), $key);
        }

        if (!isset($intersecting[$key]) && is_array($value)) {
            $resultArray[] = commitWithoutCrossing($firstArray, $secondArray, $key);
        }
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

function buildResult(array $resultArray, string $format): string
{
    $result = [];
    $resultString = '';

    switch ($format) {
        case 'stylish':
            $result = formatStylish($resultArray, $result);
            foreach ($result as $item) {
                $resultString .= "\n" . $item;
            }
            return sprintf("{%s\n}", $resultString);
        case 'plain':
            dump($resultArray);
            $result = formatPlain($resultArray, $result);
            foreach ($result as $item) {
                $resultString .= "\n" . $item;
            }
            return $resultString;
    }

    return $resultString;
}

function walkArrayStylish(array $resultArray, array &$result, int $spaceCount): void
{
    foreach ($resultArray as $key => $item) {
        if (is_array($item)) {
            $result[] = sprintf("%*s%s: {", $spaceCount, ' ', $key);
            walkArrayStylish($item, $result, $spaceCount + 4);
            $result[] = sprintf("%*s}", $spaceCount, ' ');
        }

        if (!is_array($item)) {
            $result[] = sprintf("%*s%s: %s", $spaceCount, ' ', $key, $item);
        }
    }
}

function formatStylish(array $resultArray, array &$result, int $spaceCount = 4): array
{
    foreach ($resultArray as $item) {
        if ($item['action'] === 'added') {
            if (is_array($item['value'])) {
                $result[] = sprintf("%*s+ %s: {", $spaceCount - 2, ' ', $item['key']);
                walkArrayStylish($item['value'], $result, $spaceCount + 4);
                $result[] = sprintf("%*s}", $spaceCount, ' ');
            }

            if (!is_array($item['value'])) {
                $result[] = sprintf("%*s+ %s: %s", $spaceCount - 2, ' ', $item['key'], $item['value']);
            }
        }

        if ($item['action'] === 'removed') {
            if (is_array($item['value'])) {
                $result[] = sprintf("%*s- %s: {", $spaceCount - 2, ' ', $item['key']);
                walkArrayStylish($item['value'], $result, $spaceCount + 4);
                $result[] = sprintf("%*s}", $spaceCount, ' ');
            }

            if (!is_array($item['value'])) {
                $result[] = sprintf("%*s- %s: %s", $spaceCount - 2, ' ', $item['key'], $item['value']);
            }
        }

        if ($item['action'] === 'nothing') {
            if (is_array($item['value'])) {
                $result[] = sprintf("%*s %s: {", $spaceCount - 2, ' ', $item['key']);
                walkArrayStylish($item['value'], $result, $spaceCount + 4);
                $result[] = sprintf("%*s}", $spaceCount, ' ');
            }

            if (!is_array($item['value'])) {
                $result[] = sprintf("%*s%s: %s", $spaceCount, ' ', $item['key'], $item['value']);
            }
        }

        if ($item['action'] === 'update') {
            if ($item['value'] === null) {
                if (!is_array($item['value'])) {
                    if (!is_array($item['from'])) {
                        $result[] = sprintf("%*s- %s: %s", $spaceCount - 2, ' ', $item['key'], $item['from']);
                    }
                    if (is_array($item['from'])) {
                        $result[] = sprintf("%*s- %s: {", $spaceCount - 2, ' ', $item['key']);
                        walkArrayStylish($item['from'], $result, $spaceCount + 4);
                        $result[] = sprintf("%*s}", $spaceCount, ' ');
                    }
                    if (!is_array($item['to'])) {
                        $result[] = sprintf("%*s+ %s: %s", $spaceCount - 2, ' ', $item['key'], $item['to']);
                    }
                    if (is_array($item['to'])) {
                        $result[] = sprintf("%*s+ %s: {", $spaceCount - 2, ' ', $item['key']);
                        walkArrayStylish($item['to'], $result, $spaceCount + 4);
                        $result[] = sprintf("%*s}", $spaceCount, ' ');
                    }


                }
            }

            if ($item['value'] !== null) {
                $result[] = sprintf("%*s%s: {", $spaceCount, ' ', $item['key']);
                formatStylish($item['value'],  $result, $spaceCount + 4);
                $result[] = sprintf("%*s}", $spaceCount, ' ');
            }
        }
    }

    return $result;
}

function formatPlain(array $resultArray, array &$result, string $root = ''): array
{
    foreach ($resultArray as $item) {
        $prefix = empty($root) ? $item['key'] : $root . '.' . $item['key'];

        if ($item['action'] === 'added') {
            if (is_array($item['value'])) {
                $result[] = sprintf("Property '%s' was %s with value: %s", $prefix, $item['action'], '[complex value]');
            }

            if (!is_array($item['value'])) {
                $result[] = sprintf("Property '%s' was %s with value: %s", $prefix, $item['action'], $item['value']);
            }
        }

        if ($item['action'] === 'removed') {
            $result[] = sprintf("Property '%s' was %s", $prefix, $item['action']);
        }

        if ($item['action'] === 'update') {
            if ($item['value'] === null) {
                if (!is_array($item['value'])) {
                    if (!is_array($item['from']) && !is_array($item['to'])) {
                        $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], $item['from'], $item['to']);
                    }
                    if (!is_array($item['from']) && is_array($item['to'])) {
                        $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], $item['from'], '[complex value]');
                    }
                    if (is_array($item['from']) && !is_array($item['to'])) {
                        $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], '[complex value]', $item['to']);
                    }
                    if (is_array($item['from']) && is_array($item['to'])) {
                        $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], '[complex value]', '[complex value]');
                    }
                }
            }

            if ($item['value'] !== null) {
                formatPlain($item['value'], $result, $root);
            }
        }
    }

    return $result;
}


function convertToString(mixed $value): string|array
{
    if (is_bool($value)) {
        $value = $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        $value = 'null';
    }

    if (is_array($value)) {
        return $value;
    }

    return (string) $value;
}