<?php
session_start();

if(!isset($_SESSION['name']) || !isset($_SESSION['game'])) {
    http_response_code(404);
    exit();
}
if(!isset($_SESSION['team']) || !isset($_SESSION['team'])) {
    http_response_code(404);
    exit();
}

$winner = false;
$cell = intval($_GET['cell']);
if($cell != 0) {
    $cell = $cell - 1;

    if($cell < 0 || $cell > 8 || $_SESSION['game'][$cell] != 0) {
        http_response_code(400);
        exit();
    }

    $team = $_SESSION['team'];

    $_SESSION['game'][$cell] = $team;

    $botMakeMove = true;
    for($i = 0; $i <= 8; $i++) {
        if($_SESSION['game'][$i] == 0) $botMakeMove = false;
    }
    
    while($botMakeMove == false) {
        $botMove = rand(0, 8);
        if($_SESSION['game'][$botMove] == 0) {
            if ($team == 1) $_SESSION['game'][$botMove] = 2;
            else $_SESSION['game'][$botMove] = 1;
            $botMakeMove = true;
        }
    }

    if ($_SESSION['game'][0] == 1 && $_SESSION['game'][1] == 1 && $_SESSION['game'][2] == 1 ||
        $_SESSION['game'][3] == 1 && $_SESSION['game'][4] == 1 && $_SESSION['game'][5] == 1 ||
        $_SESSION['game'][6] == 1 && $_SESSION['game'][7] == 1 && $_SESSION['game'][8] == 1 ||
        $_SESSION['game'][0] == 1 && $_SESSION['game'][3] == 1 && $_SESSION['game'][6] == 1 ||
        $_SESSION['game'][1] == 1 && $_SESSION['game'][4] == 1 && $_SESSION['game'][7] == 1 ||
        $_SESSION['game'][2] == 1 && $_SESSION['game'][5] == 1 && $_SESSION['game'][8] == 1 ||
        $_SESSION['game'][0] == 1 && $_SESSION['game'][4] == 1 && $_SESSION['game'][8] == 1 ||
        $_SESSION['game'][6] == 1 && $_SESSION['game'][4] == 1 && $_SESSION['game'][2] == 1) {
        $winner = [
            'status' => 'win',
            'team' => $_SESSION['team'],
            'winner' => 1
        ];
        if ($team == $winner['winner']) $winner['message'] = 'Вы выйграли!';
        else $winner['message'] = 'Вы проиграли!';
    }
    else if ($_SESSION['game'][0] == 2 && $_SESSION['game'][1] == 2 && $_SESSION['game'][2] == 2 ||
        $_SESSION['game'][3] == 2 && $_SESSION['game'][4] == 2 && $_SESSION['game'][5] == 2 ||
        $_SESSION['game'][6] == 2 && $_SESSION['game'][7] == 2 && $_SESSION['game'][8] == 2 ||
        $_SESSION['game'][0] == 2 && $_SESSION['game'][3] == 2 && $_SESSION['game'][6] == 2 ||
        $_SESSION['game'][1] == 2 && $_SESSION['game'][4] == 2 && $_SESSION['game'][7] == 2 ||
        $_SESSION['game'][2] == 2 && $_SESSION['game'][5] == 2 && $_SESSION['game'][8] == 2 ||
        $_SESSION['game'][0] == 2 && $_SESSION['game'][4] == 2 && $_SESSION['game'][8] == 2 ||
        $_SESSION['game'][6] == 2 && $_SESSION['game'][4] == 2 && $_SESSION['game'][2] == 2) {
        $winner = [
            'status' => 'win',
            'team' => $_SESSION['team'],
            'winner' => 2
        ];
        if ($team == $winner['winner']) $winner['message'] = 'Вы выйграли!';
        else $winner['message'] = 'Вы проиграли!';
    }
}

$game = [
    'status' => 'proccess',
    'team' => $_SESSION['team'],
    'grid' => []
];

for($i = 0; $i <= 8; $i++) {
    if(!isset($_SESSION['game'][$i])) {
        http_response_code(404);
        exit();
    }

    $game['grid'][$i] = $_SESSION['game'][$i];
}

$allMoves = true;
for($i = 0; $i <= 8; $i++) {
    if($_SESSION['game'][$i] == 0) $allMoves = false;
}

if($allMoves && !$winner) {
    for($i = 0; $i <= 8; $i++) {
        $_SESSION['game'][$i] = 0;
    }
    echo json_encode([
        'status' => 'draw',
        'team' => $_SESSION['team'],
        'grid' => $game['grid'],
        'message' => 'Ничья'
    ]);
    exit();
}

if ($winner) {
    for($i = 0; $i <= 8; $i++) {
        $_SESSION['game'][$i] = 0;
    }
    $winner['grid'] = $game['grid'];
    
    $mysqli = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
    if (mysqli_connect_errno()) {
        printf("Подключение невозможно: %s\n", mysqli_connect_error());
        exit();
    }

    $gridSerialize = serialize($game['grid']);

    $stmt = $mysqli->prepare("INSERT INTO games(name,grid,team,winner) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssdd', $_SESSION['name'], $gridSerialize, $winner['team'], $winner['winner']);

    $stmt->execute();
    $stmt->close();

    if($result = $mysqli->query(sprintf('SELECT level FROM players WHERE name = "%s"', $_SESSION['name']))) {
        if($result->num_rows < 1) {
            $stmt = $mysqli->prepare("INSERT INTO players(name,level) VALUES (?, ?)");

            if($winner['team'] == $winner['winner']) $level = 2;
            else $level = 1;

            $stmt->bind_param('sd', $_SESSION['name'], $level);

            $stmt->execute();
            $stmt->close();
        } else {
            while($row = $result->fetch_assoc()){
                $stmt = $mysqli->prepare("UPDATE players SET level = ? WHERE name = ?");

                if($winner['team'] == $winner['winner']) $level = $row['level'] + 1;
                else {
                    if ($row['level'] > 1) $level = $row['level'] - 1;
                }

                $stmt->bind_param('ds', $level, $_SESSION['name']);

                $stmt->execute();
                $stmt->close();
            }
        }
    } else exit();

    $mysqli->close();

    http_response_code(200);
    echo json_encode($winner);
    exit();
}

echo json_encode($game);
