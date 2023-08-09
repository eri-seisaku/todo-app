<?php

namespace work;

require_once(__DIR__ . '/../app/Bootstrap.class.php');

use work\app\Bootstrap;
use work\app\lib\PDODatabase;
use work\app\lib\Session;
use work\app\Todo;
use work\app\Image;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$session = new Session($db);
$todo = new Todo($db, $session);
$image = new Image($db, $session);

// セッションが存在しない場合はlogin.phpを読み込む
if (!isset($_SESSION['res']) || $_SESSION['res'] === false) {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];
$result = $todo->processPost();
$fileName = $image->getFileName($userId);
$err_message = $_SESSION['err_message'] ?? null;

if ($result === true) {
  unset($_SESSION['err_message']);
  header('Location: index.php');
  exit;
}

if ($result === false) {
  header('Location: post.php');
  exit;
}

unset($_SESSION['err_message']);

$template = 'post.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'user_id' => $_SESSION['user_id'],
  'err_message' => $err_message,
  'file_name' => $fileName[0]['icon_filename'],
]);

