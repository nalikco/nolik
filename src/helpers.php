<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(mixed $value): void
{
    echo "<pre><code>";
    print_r($value);
    echo "</code></pre>";

    die();
}