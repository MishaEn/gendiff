<?php

namespace Differ\Formatters;

const FULL_TAB = '    ';
const HALF_TAB = '  ';
function format(array $resultArray, string $format, array $firstArray = [], array $secondArray = [])
{
    $resultString = '';
    $result = [];

    return match ($format) {
        'stylish' => sprintf("{\n%s\n}", implode("\n", formatStylish($resultArray, $result))),
        'plain' => implode("\n", formatPlain($resultArray, $result)),
        'json' => json_encode(formatJson($firstArray, $secondArray, $result)),
        default => $resultString,
    };
}

function formatStylish(array $resultArray, array &$result, int $depth = 1): array
{
    foreach ($resultArray as $item) {
        switch ($item['action']) {
            case 'added':
                formatStylishSimpleAction($result, $item, $depth, '+ ');
                break;
            case 'removed':
                formatStylishSimpleAction($result, $item, $depth, '- ');
                break;
            case 'nothing':
                formatStylishSimpleAction($result, $item, $depth);
                break;
            case 'updated':
                formatStylishActionUpdate($result, $item, $depth);
                break;
        }
    }

    return $result;
}
function formatStylishSimpleAction(array &$result, mixed $item, int $depth, string $sign = ''): void
{
    if (is_array($item['value'])) {
        formatDeep($result, $item, $depth, $sign);

        return;
    }

    $spaces = createTabs($depth, !empty($sign));

    if (!empty($sign)) {
        $spaces = createTabs($depth + ($depth - 1), !empty($sign));
    }

    $result[] = sprintf("%s%s%s: %s", $spaces, $sign, $item['key'], $item['value']);
}

function formatStylishActionUpdate(array &$result, mixed $item, int $depth): void
{
    $spaces = createTabs($depth);
    if ($item['value'] === null && !is_array($item['value'])) {
        if (is_array($item['from'])) {
            $item['value'] = $item['from'];
            formatDeep($result, $item, $depth, '- ');
        }

        if (is_array($item['to'])) {
            $item['value'] = $item['to'];
            formatDeep($result, $item, $depth, '+ ');
        }

        if (!is_array($item['from'])) {
            $spaces = createTabs($depth + ($depth - 1), true);
            $result[] = sprintf("%s- %s: %s", $spaces, $item['key'], $item['from']);
        }

        if (!is_array($item['to'])) {
            $spaces = createTabs($depth + ($depth - 1), true);
            $result[] = sprintf("%s+ %s: %s", $spaces, $item['key'], $item['to']);
        }



        return;
    }

    $result[] = sprintf("%s%s: {", $spaces, $item['key']);
    formatStylish($item['value'], $result, ++$depth);
    $result[] = sprintf("%s}", $spaces);
}

function formatDeep(array &$result, array $item, int $depth, string $sign = ''): void
{
    $spaces = createTabs($depth, !empty($sign));
    if (!empty($sign)) {
        $spaces = createTabs($depth + 1, true);
    }

    if ($depth === 1) {
        $spaces = '  ';
    }

    $result[] = sprintf("%s%s%s: {", $spaces, $sign, $item['key']);
    walkArrayStylish($item['value'], $result, $depth);
    $spaces = createTabs($depth);
    $result[] = sprintf("%s}", $spaces);
}

function walkArrayStylish(array $resultArray, array &$result, int $depth): void
{
    ++$depth;
    foreach ($resultArray as $key => $item) {
        $value['key'] = $key;
        $value['value'] = $item;
        if (is_array($item)) {
            formatDeep($result, $value, $depth);
            continue;
        }
        $spaces = createTabs($depth);
        $result[] = sprintf("%s%s: %s", $spaces, $key, $item);
    }
}

function formatPlain(array $resultArray, array &$result, int $dept = 0, string $root = ''): array
{
    foreach ($resultArray as $item) {
        $prefix = $dept === 0 ? $item['key'] : $root . '.' . $item['key'];
        $prefixArray = explode('.', $prefix);
        switch ($item['action']) {
            case 'added':
                if ($prefixArray[$dept] !== $item['key']) {
                    $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                }
                $value = !is_array($item['value']) ? wrapQuotes($item['value']) : '[complex value]';
                $result[] = sprintf("Property '%s' was %s with value: %s", $prefix, $item['action'], $value);
                ;
                break;
            case 'removed':
                if ($prefixArray[$dept] !== $item['key']) {
                    $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                }
                $result[] = sprintf("Property '%s' was %s", $prefix, $item['action']);
                break;
            case 'updated':
                if ($item['value'] === null) {
                    if ($prefixArray[$dept] !== $item['key']) {
                        $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                    }
                    $to = !is_array($item['to']) ? wrapQuotes($item['to']) : '[complex value]';
                    $from = !is_array($item['from']) ? wrapQuotes($item['from']) : '[complex value]';
                    $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], $from, $to);
                    break;
                }
                $root = $dept === 0 ? $item['key'] : $prefix;
                formatPlain($item['value'], $result, $dept + 1, $root);
                break;
        }
    }
    return $result;
}

function formatJson($firstArray, $secondArray, $result): array
{
    return array_merge($firstArray, $secondArray);
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
function convertArray(array $array): array
{
    foreach ($array as $key => $item) {
        if (is_array($item)) {
            $array[$key] = convertArray($item);

            continue;
        }
        $array[$key] = convertToString($item);
    }

    return $array;
}
function createTabs(int $depth, bool $isSign = false): string
{
    return str_repeat($isSign ? HALF_TAB : FULL_TAB, $depth);
}

function wrapQuotes(string $item): string
{
    if ($item !== 'false' && $item !== 'true' && $item !== 'null' && !is_numeric($item)) {
        return "'" . $item . "'";
    }
    return $item;
}
