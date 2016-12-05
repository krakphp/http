<?php

namespace Krak\Mw\Http\Util;

use function iter\reduce;

/** create an array from the given array that have keys
    that match the prefix given.

    usage:

    ```
    $a = [
        'a.value' => 1,
        'a.value2' => 2,
        'key1' => 3,
    ];
    $b = arrayFromPrefix($a, 'a.');
    // array_keys($b) == ['a.value', 'a.value2']
    ```
*/
function arrayFromPrefix($array, $prefix) {
    return reduce(function($acc, $value, $key) use ($prefix) {
        if (strpos($key, $prefix) === 0) {
            $acc[$key] = $value;
        }

        return $acc;
    }, $array, []);
}

function isTuple($tuple, ...$types) {
    if (!is_array($tuple) || count($tuple) != count($types)) {
        return false;
    }


    foreach ($types as $i => $type) {
        if (
            !isset($tuple[$i]) ||
            !($type == "any" || gettype($tuple[$i]) == $type)
        ) {
            return false;
        }
    }

    return true;
}

function joinUri($a, $b) {
    if ($b == '/') {
        return $a;
    }

    return rtrim($a, '/') . '/' . ltrim($b, '/');
}
