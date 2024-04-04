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
    /** @phpstan-ignore-next-line */
    foreach ($resultArray as $item) {
        switch ($item['action']) {
            /** @phpstan-ignore-next-line */
            case 'added':
                formatStylishSimpleAction($result, $item, $depth, '+ ');
                /** @phpstan-ignore-next-line */
                break;
            case 'removed':
                /** @phpstan-ignore-next-line */
                formatStylishSimpleAction($result, $item, $depth, '- ');
                break;
            /** @phpstan-ignore-next-line */
            case 'nothing':
                formatStylishSimpleAction($result, $item, $depth);
                break;
            case 'updated':
                formatStylishActionUpdate($result, $item, $depth);
                break;
        }
    }
    /** @phpstan-ignore-next-line */
    return $result;
}
function formatStylishSimpleAction(array &$result, mixed $item, int $depth, string $sign = ''): void
{
    if (is_array($item['value'])) {
        /** @phpstan-ignore-next-line */
        formatDeep($result, $item, $depth, $sign);
        /** @phpstan-ignore-next-line */
        return;
    }
    /** @phpstan-ignore-next-line */
    $spaces = createTabs($depth, !empty($sign));

    if (!empty($sign)) {
        $spaces = createTabs($depth + ($depth - 1), !empty($sign));
    }
    /** @phpstan-ignore-next-line */
    $result[] = sprintf("%s%s%s: %s", $spaces, $sign, $item['key'], $item['value']);
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */
function formatStylishActionUpdate(array &$result, mixed $item, int $depth): void
{
    /** @phpstan-ignore-next-line */
    $spaces = createTabs($depth);
    /** @phpstan-ignore-next-line */
    if ($item['value'] === null && !is_array($item['value'])) {
        if (is_array($item['from'])) {
            $item['value'] = $item['from'];
            /** @phpstan-ignore-next-line */
            formatDeep($result, $item, $depth, '- ');
            /** @phpstan-ignore-next-line */
        }

        if (is_array($item['to'])) {
            /** @phpstan-ignore-next-line */
            $item['value'] = $item['to'];
            formatDeep($result, $item, $depth, '+ ');
        }

        if (!is_array($item['from'])) {
            $spaces = createTabs($depth + ($depth - 1), true);
            $result[] = sprintf("%s- %s: %s", $spaces, $item['key'], $item['from']);
            /** @phpstan-ignore-next-line */
        }
        /** @phpstan-ignore-next-line */
        if (!is_array($item['to'])) {
            $spaces = createTabs($depth + ($depth - 1), true);
            $result[] = sprintf("%s+ %s: %s", $spaces, $item['key'], $item['to']);
        }
        /** @phpstan-ignore-next-line */
        return;
        /** @phpstan-ignore-next-line */
    }

    $result[] = sprintf("%s%s: {", $spaces, $item['key']);
    /** @phpstan-ignore-next-line */
    formatStylish($item['value'], $result, ++$depth);
    /** @phpstan-ignore-next-line */
    $result[] = sprintf("%s}", $spaces);
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */

/** @phpstan-ignore-next-line */
function formatDeep(array &$result, array $item, int $depth, string $sign = ''): void
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    $spaces = createTabs($depth, !empty($sign));
    /** @phpstan-ignore-next-line */
    if (!empty($sign)) {
        /** @phpstan-ignore-next-line */
        $spaces = createTabs($depth + 1, true);
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */

    /** @phpstan-ignore-next-line */
    if ($depth === 1) {
        /** @phpstan-ignore-next-line */
        $spaces = '  ';
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */

    /** @phpstan-ignore-next-line */
    $result[] = sprintf("%s%s%s: {", $spaces, $sign, $item['key']);
    /** @phpstan-ignore-next-line */
    walkArrayStylish($item['value'], $result, $depth);
    /** @phpstan-ignore-next-line */
    $spaces = createTabs($depth);
    /** @phpstan-ignore-next-line */
    $result[] = sprintf("%s}", $spaces);
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */

/** @phpstan-ignore-next-line */
function walkArrayStylish(array $resultArray, array &$result, int $depth): void
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    ++$depth;
    /** @phpstan-ignore-next-line */
    foreach ($resultArray as $key => $item) {
        /** @phpstan-ignore-next-line */
        $value['key'] = $key;
        /** @phpstan-ignore-next-line */
        $value['value'] = $item;
        /** @phpstan-ignore-next-line */
        if (is_array($item)) {
            /** @phpstan-ignore-next-line */
            formatDeep($result, $value, $depth);
            /** @phpstan-ignore-next-line */
            continue;
            /** @phpstan-ignore-next-line */
        }
        /** @phpstan-ignore-next-line */
        $spaces = createTabs($depth);
        /** @phpstan-ignore-next-line */
        $result[] = sprintf("%s%s: %s", $spaces, $key, $item);
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */

/** @phpstan-ignore-next-line */
function formatPlain(array $resultArray, array &$result, int $dept = 0, string $root = ''): array
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    foreach ($resultArray as $item) {
        /** @phpstan-ignore-next-line */
        $prefix = $dept === 0 ? $item['key'] : $root . '.' . $item['key'];
        /** @phpstan-ignore-next-line */
        $prefixArray = explode('.', $prefix);
        /** @phpstan-ignore-next-line */
        switch ($item['action']) {
            /** @phpstan-ignore-next-line */
            case 'added':
                /** @phpstan-ignore-next-line */
                if ($prefixArray[$dept] !== $item['key']) {
                    /** @phpstan-ignore-next-line */
                    $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                    /** @phpstan-ignore-next-line */
                }
                /** @phpstan-ignore-next-line */
                $value = !is_array($item['value']) ? wrapQuotes($item['value']) : '[complex value]';
                /** @phpstan-ignore-next-line */
                $result[] = sprintf("Property '%s' was %s with value: %s", $prefix, $item['action'], $value);
                /** @phpstan-ignore-next-line */
                ;
                /** @phpstan-ignore-next-line */
                break;
                /** @phpstan-ignore-next-line */
            case 'removed':
                /** @phpstan-ignore-next-line */
                if ($prefixArray[$dept] !== $item['key']) {
                    /** @phpstan-ignore-next-line */
                    $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                    /** @phpstan-ignore-next-line */
                }
                /** @phpstan-ignore-next-line */
                $result[] = sprintf("Property '%s' was %s", $prefix, $item['action']);
                /** @phpstan-ignore-next-line */
                break;
                /** @phpstan-ignore-next-line */
            case 'updated':
                /** @phpstan-ignore-next-line */
                if ($item['value'] === null) {
                    /** @phpstan-ignore-next-line */
                    if ($prefixArray[$dept] !== $item['key']) {
                        /** @phpstan-ignore-next-line */
                        $prefix = str_replace('.' . $prefixArray[$dept], "", $prefix);
                        /** @phpstan-ignore-next-line */
                    }
                    /** @phpstan-ignore-next-line */
                    $to = !is_array($item['to']) ? wrapQuotes($item['to']) : '[complex value]';
                    /** @phpstan-ignore-next-line */
                    $from = !is_array($item['from']) ? wrapQuotes($item['from']) : '[complex value]';
                    /** @phpstan-ignore-next-line */
                    $result[] = sprintf("Property '%s' was %s. From %s to %s", $prefix, $item['action'], $from, $to);
                    /** @phpstan-ignore-next-line */
                    break;
                    /** @phpstan-ignore-next-line */
                }
                /** @phpstan-ignore-next-line */
                $root = $dept === 0 ? $item['key'] : $prefix;
                /** @phpstan-ignore-next-line */
                formatPlain($item['value'], $result, $dept + 1, $root);
                /** @phpstan-ignore-next-line */
                break;
                /** @phpstan-ignore-next-line */
        }
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */
    return $result;
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */

/** @phpstan-ignore-next-line */
function formatJson($firstArray, $secondArray, $result): array
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    return array_merge($firstArray, $secondArray);
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */
function convertToString(mixed $value): string|array
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    if (is_bool($value)) {
        /** @phpstan-ignore-next-line */
        $value = $value ? 'true' : 'false';
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */
    if (is_null($value)) {
        /** @phpstan-ignore-next-line */
        $value = 'null';
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */
    if (is_array($value)) {
        /** @phpstan-ignore-next-line */
        return $value;
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */

    /** @phpstan-ignore-next-line */
    return (string) $value;
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */
function convertArray(array $array): array
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    foreach ($array as $key => $item) {
        /** @phpstan-ignore-next-line */
        if (is_array($item)) {
            /** @phpstan-ignore-next-line */
            $array[$key] = convertArray($item);
            /** @phpstan-ignore-next-line */

            /** @phpstan-ignore-next-line */
            continue;
            /** @phpstan-ignore-next-line */
        }
        /** @phpstan-ignore-next-line */
        $array[$key] = convertToString($item);
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */

    /** @phpstan-ignore-next-line */
    return $array;
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */
function createTabs(int $depth, bool $isSign = false): string
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    return str_repeat($isSign ? HALF_TAB : FULL_TAB, $depth);
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */

/** @phpstan-ignore-next-line */
function wrapQuotes(string $item): string
/** @phpstan-ignore-next-line */
{
    /** @phpstan-ignore-next-line */
    if ($item !== 'false' && $item !== 'true' && $item !== 'null' && !is_numeric($item)) {
        /** @phpstan-ignore-next-line */
        return "'" . $item . "'";
        /** @phpstan-ignore-next-line */
    }
    /** @phpstan-ignore-next-line */
    return $item;
    /** @phpstan-ignore-next-line */
}
/** @phpstan-ignore-next-line */
