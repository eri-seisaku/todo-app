<?php

namespace work;

require_once(__DIR__ . '/../app/Bootstrap.class.php');

use work\app\Bootstrap;
use work\app\lib\PDODatabase;
use work\app\lib\Session;
use work\app\Todo;
use work\app\Auth;
use work\app\Image;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$session = new Session($db);
$todo = new Todo($db, $session);
$auth = new Auth($db, $session);
$image = new Image($db, $session);

// セッションが存在しない場合はlogin.phpを読み込む
if (!isset($_SESSION['res']) || $_SESSION['res'] === false) {
  header('Location: login.php');
  exit;
}

// ユーザーのTodoを取得
$userId = $_SESSION['user_id'];
$todos = $todo->getUser($userId);
$fileName = $image->getFileName($userId);

$action = isset($_GET['action']) ? $_GET['action'] : '';

$todoActions = array('add', 'toggle', 'delete', 'clear', 'update');
$authActions = array('signup', 'login', 'logout', 'update', 'delete');


if (in_array($action, $todoActions)) {
  $result = $todo->processPost();
  $_SESSION['err_message'] = $result ? '処理中に問題が発生しました。' : null;
  header('Location: index.php');
  exit;

} elseif (in_array($action, $authActions)) {
  $result = $auth->processPost();
  $_SESSION['err_message'] = $result ? '処理中に問題が発生しました。' : null;
  header('Location: login.php');
  exit;
}

$message = $_SESSION['message'] ?? null;
$err_message = $_SESSION['err_message'] ?? null;

unset($_SESSION['message'], $_SESSION['err_message']);

$template = 'index.html.twig';
$template = $twig->load($template);

echo $template->render([
  'todos' => $todos,
  'token' => $_SESSION['token'],
  'user_id' => $_SESSION['user_id'],
  'message' => $message,
  'err_message' => $err_message,
  'file_name' => $fileName[0]['icon_filename'],
  'current_page' => 'Home',
]);
