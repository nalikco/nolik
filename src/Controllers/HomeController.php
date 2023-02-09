<?php

namespace App\Controllers;

use App\Session;

class HomeController extends Controller
{
    public function home(): void
    {
        if(Session::get("name")) {
            Session::deleteAll();
        }

        $players = [];
        if($rows = $this->app->db->getRows("SELECT name,level FROM players ORDER BY level DESC")) {
            foreach($rows as $row) {
                $players[] = [
                    'name' => $row['name'],
                    'level' => $row['level']
                ];
            }
        }

        include(ROOT . "templates/parts/head.php");
        include(ROOT . "templates/index.php");
        include(ROOT . "templates/parts/footer.php");
    }
}