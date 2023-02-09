<?php

namespace App;

use JetBrains\PhpStorm\NoReturn;

class Response
{
    #[NoReturn] public static function notFound(): void
    {
        http_response_code(404);
        echo "not found";
        die();
    }

    #[NoReturn] public static function redirect(string $url): void
    {
        header('Location: '.$url);
        die();
    }
}