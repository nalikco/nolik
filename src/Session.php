<?php

namespace App;

class Session
{
    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function deleteAll(): void
    {
        unset($_SESSION['name']);
        unset($_SESSION['team']);
        if(isset($_SESSION['game'])) for($i = 0; $i <= 8; $i++) {
            if(isset($_SESSION['game'][$i])) unset($_SESSION['game'][$i]);
        }
    }
}