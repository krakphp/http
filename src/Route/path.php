<?php

namespace Krak\Http\Route;

function concatJoinPath() {
    return function($a, $b) {
        return $a . $b;
    };
}

/** removes the duplicate slashes when joining paths */
function rmDupSlashesJoinPath() {
    return function($a, $b) {
        // if b is empty, don't do any joining
        if (!$b) {
            return $a;
        }

        return rtrim($a, '/') . '/' . ltrim($b, '/');
    };
}
