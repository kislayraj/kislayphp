<?php
function env_string($name, $default) {
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function env_int($name, $default) {
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }
    return (int) $value;
}

function require_extension($name) {
    if (!extension_loaded($name)) {
        fwrite(STDERR, "Missing extension: {$name}\n");
        exit(1);
    }
}

function require_extensions(array $names) {
    foreach ($names as $name) {
        require_extension($name);
    }
}
