<?php
session_start();

if(isset($_SESSION['name'])) {
    unset($_SESSION['name']);
    unset($_SESSION['team']);
    if(isset($_SESSION['game'])) for($i = 0; $i <= 8; $i++) {
        if(isset($_SESSION['game'][$i])) unset($_SESSION['game'][$i]);
    }
}

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));
$mysqli = new mysqli($url["host"], $url["user"], $url["pass"], substr($url["path"], 1));
if (mysqli_connect_errno()) {
    printf("Подключение невозможно: %s\n", mysqli_connect_error());
    exit();
}

$players = [];
if($result = $mysqli->query(sprintf('SELECT name,level FROM players ORDER BY level DESC'))) {
    while($row = $result->fetch_assoc()){
        $players[] = [
          'name' => $row['name'],
          'level' => $row['level']
        ];
    }
} else exit();
$mysqli->close();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Крестики-нолики</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500&display=swap" rel="stylesheet">
</head>
<body class="h-full" style="font-family: 'Inter', sans-serif;">
<div class="flex min-h-full items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="w-full max-w-md space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">Крестики-нолики</h2>
    </div>
    <form class="mt-8 space-y-6" action="/game.php" method="GET">
      <div class="-space-y-px rounded-md shadow-sm">
        <div>
          <input name="name" type="text" required class="relative block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-green-500 focus:outline-none focus:ring-green-500 sm:text-sm" placeholder="Введите имя...">
        </div>
      </div>
      <div class="-space-y-px rounded-md shadow-sm">
        <div>
          <label for="countries" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Выберите команду</label>
          <select name="team" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <option selected value="1">0</option>
            <option value="2">x</option>
          </select>
        </div>
      </div>
      <div>
        <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
          Начать игру
        </button>
      </div>
    </form>
    <?php if(count($players) > 0){ ?>
      <div>
      <div class="overflow-x-auto relative">
      <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                  <th scope="col" class="py-3 px-6">
                      Имя
                  </th>
                  <th scope="col" class="py-3 px-6">
                      Уровень
                  </th>
              </tr>
          </thead>
          <tbody>
              <?php foreach($players as $player){ ?>
              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                  <td class="py-4 px-6">
                      <?=$player['name']?>
                  </td>
                  <td class="py-4 px-6">
                    <?=$player['level']?>
                  </td>
              </tr>
              <?php } ?>
          </tbody>
      </table>
  </div>
      </div>
    <?php } ?>
  </div>
</div>
</body>
</html>