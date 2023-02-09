<?php

namespace App;

class App
{
    public Database $db;
    private Router $router;

    public function __construct()
    {
        $this->db = new Database("sqlite:".ROOT."database.sqlite");
        $this->router = new Router();
    }

    public function serve(): void
    {
        $matched = $this->router->match($_SERVER["PATH_INFO"] ?? "/", $_SERVER["REQUEST_METHOD"]);
        if(!$matched) Response::notFound();

        $controllerClass = $matched["controller"];
        $controllerMethod = $matched["method"];

        $controller = new $controllerClass($this);
        $controller->$controllerMethod();
    }
}