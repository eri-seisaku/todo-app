<?php

namespace work;

require_once(__DIR__ . '/../app/Bootstrap.class.php');

use work\app\Bootstrap;
use work\app\lib\PDODatabase;
use work\app\lib\Session;
use work\app\Auth;
use work\app\Todo;
use work\app\Image;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$session = new Session($db);
$auth = new Auth($db, $session);
$todo = new Todo($db, $session);
$image = new Image($db, $session);

// セッションが存在しない場合はlogin.phpを読み込む
if (!isset($_SESSION['res']) || $_SESSION['res'] === false) {
  header('Location: login.php');
  exit;
}

// ユーザーのTodoを取得
$userId = $_SESSION['user_id'];
$fileName = $image->getFileName($userId);
$allList = $todo->getFileAndTodo();

$template = 'list.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'all_list' => $allList,
  'file_name' => $fileName[0]['icon_filename'],
  'current_page' => 'List',
]);

