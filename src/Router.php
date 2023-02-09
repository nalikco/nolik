<?php

namespace App;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->initRoutes();
    }

    private function initRoutes(): void
    {
        $this->routes = [
            "GET:/" => ["controller" => "\\App\\Controllers\\HomeController", "method" => "home"],
            "GET:/game" => ["controller" => "\\App\\Controllers\\GameController", "method" => "game"],
            "POST:/api/move" => ["controller" => "\\App\\Controllers\\GameController", "method" => "apiMove"],
        ];
    }

    public function match(string $uri, string $method): array|null
    {
        foreach ($this->routes as $pattern => $route)
        {
            $patternParts = explode(":", $pattern);

            if($method == $patternParts[0] && $uri == $patternParts[1]) {
                return $route;
            }
        }

        return null;
    }
}