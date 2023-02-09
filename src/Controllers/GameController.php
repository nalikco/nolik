<?php

namespace App\Controllers;

use App\Response;
use App\Session;

class GameController extends Controller
{
    public function game(): void
    {
        $name = $_GET['name'] ?? "";
        $team = $_GET['team'] ?? "";

        if(!Session::get("name")) {
            Session::set("name", $name);

            if($team == '') Response::notFound();
            $team = intval($team);
            if($team != 1 && $team != 2) Response::notFound();

            Session::set("team", $team);

            $grid = [];
            for($i = 0; $i <= 8; $i++) {
                $grid[$i] = 0;
            }
            Session::set("game", $grid);

            Response::redirect("/game");
        }

        $team = Session::get("team");
        $name = Session::get("name");
        $teamStr = '';

        if ($team == 1) $teamStr = '0';
        else $teamStr = 'x';

        include(ROOT . "templates/parts/head.php");
        include(ROOT . "templates/game.php");
        include(ROOT . "templates/parts/footer.php");
    }

    public function apiMove(): void
    {
        if(!Session::get("name") || !Session::get("game") || !Session::get("team")) Response::notFound();

        $winner = false;
        $cell = intval($_GET['cell']);

        if($cell != 0) {
            $cell = $cell - 1;

            if($cell < 0 || $cell > 8 || Session::get("game")[$cell] != 0) {
                http_response_code(400);
                exit();
            }

            $team = Session::get("team");

            $game = Session::get("game");
            $game[$cell] = $team;
            Session::set("game", $game);

            $botMakeMove = true;
            for($i = 0; $i <= 8; $i++) {
                if(Session::get("game")[$i] == 0) $botMakeMove = false;
            }

            while($botMakeMove == false) {
                $botMove = rand(0, 8);
                if(Session::get("game")[$botMove] == 0) {
                    if ($team == 1) $game[$botMove] = 2;
                    else $game[$botMove] = 1;

                    Session::set("game", $game);
                    $botMakeMove = true;
                }
            }

            if ($game[0] == 1 && $game[1] == 1 && $game[2] == 1 ||
                $game[3] == 1 && $game[4] == 1 && $game[5] == 1 ||
                $game[6] == 1 && $game[7] == 1 && $game[8] == 1 ||
                $game[1] == 1 && $game[4] == 1 && $game[7] == 1 ||
                $game[0] == 1 && $game[3] == 1 && $game[6] == 1 ||
                $game[2] == 1 && $game[5] == 1 && $game[8] == 1 ||
                $game[0] == 1 && $game[4] == 1 && $game[8] == 1 ||
                $game[6] == 1 && $game[4] == 1 && $game[2] == 1) {
                $winner = [
                    'status' => 'win',
                    'team' => Session::get("team"),
                    'winner' => 1
                ];
                if ($team == $winner['winner']) $winner['message'] = 'Вы выйграли!';
                else $winner['message'] = 'Вы проиграли!';
            }
            else if ($game[0] == 2 && $game[1] == 2 && $game[2] == 2 ||
                $game[3] == 2 && $game[4] == 2 && $game[5] == 2 ||
                $game[6] == 2 && $game[7] == 2 && $game[8] == 2 ||
                $game[0] == 2 && $game[3] == 2 && $game[6] == 2 ||
                $game[1] == 2 && $game[4] == 2 && $game[7] == 2 ||
                $game[2] == 2 && $game[5] == 2 && $game[8] == 2 ||
                $game[0] == 2 && $game[4] == 2 && $game[8] == 2 ||
                $game[6] == 2 && $game[4] == 2 && $game[2] == 2) {
                $winner = [
                    'status' => 'win',
                    'team' => Session::get("team"),
                    'winner' => 2
                ];
                if ($team == $winner['winner']) $winner['message'] = 'Вы выйграли!';
                else $winner['message'] = 'Вы проиграли!';
            }
        }

        $game = [
            'status' => 'proccess',
            'team' => Session::get("team"),
            'grid' => []
        ];

        for($i = 0; $i <= 8; $i++) {
            if(!isset(Session::get("game")[$i])) {
                http_response_code(404);
                exit();
            }

            $game['grid'][$i] = Session::get("game")[$i];
        }

        $allMoves = true;
        for($i = 0; $i <= 8; $i++) {
            if(Session::get("game")[$i] == 0) $allMoves = false;
        }

        if($allMoves && !$winner) {
            for($i = 0; $i <= 8; $i++) {
                Session::get("game")[$i] = 0;
            }
            echo json_encode([
                'status' => 'draw',
                'team' => Session::get("team"),
                'grid' => $game['grid'],
                'message' => 'Ничья'
            ]);
            exit();
        }

        if ($winner) {
            $sessionGame = Session::get("game");
            for($i = 0; $i <= 8; $i++) {
                $sessionGame[$i] = 0;
            }
            Session::set("game", $sessionGame);

            $winner['grid'] = $game['grid'];

            $gridSerialize = serialize($game['grid']);

            $this->app->db->execute("INSERT INTO games(name,grid,team,winner) VALUES (?, ?, ?, ?)", [
                Session::get("name"), $gridSerialize, $winner['team'], $winner['winner']
            ]);

            if($rows = $this->app->db->getRows("SELECT level FROM players WHERE name = ?", [Session::get("name")])){
                if(count($rows) < 1) {
                    if($winner['team'] == $winner['winner']) $level = 2;
                    else $level = 1;

                    $this->app->db->execute("INSERT INTO players(name,level) VALUES (?, ?)", [
                        $_SESSION['name'], $level
                    ]);
                } else {
                    foreach($rows as $row) {
                        if($winner['team'] == $winner['winner']) $level = $row['level'] + 1;
                        else {
                            if ($row['level'] > 1) $level = $row['level'] - 1;
                            else $level = 1;
                        }

                        $this->app->db->execute("UPDATE players SET level = ? WHERE name = ?", [
                            $level, $_SESSION['name']
                        ]);
                    }
                }
            } else exit();

            http_response_code(200);
            echo json_encode($winner);
            exit();
        }

        echo json_encode($game);
    }
}