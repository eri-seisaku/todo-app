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

if (isset($_GET['id'])) {
  $id = filter_input(INPUT_GET, 'id');

  // IDの存在を確認する
  $count = $db->count('todos', 'id = :id', [$id]);
  if (!$count) {
    // IDが存在しない場合はindex.phpにリダイレクト
    header('Location: index.php');
    exit;
  }
  $todoData = $db->select('todos', '', 'id = :id', [$id]);
} elseif (isset($_GET['action'])) {
  $result = $todo->processPost();
  if ($result === true) {
    $_SESSION['message'] = '編集が完了しました。';
    header('Location: index.php');
    exit;
  } else {
    $_SESSION['err_message'] = '編集に失敗しました。';
    header('Location: edit.php?id=' . $id);
    exit;
  }
}

$userId = $_SESSION['user_id'];
$err_message = $_SESSION['err_message'] ?? null;
$todoData = $todoData ?? [];
$fileName = $image->getFileName($userId);

unset($_SESSION['err_message']);

$template = 'edit.html.twig';
$template = $twig->load($template);

echo $template->render([
  'todo' => $todoData[0] ?? null,
  'token' => $_SESSION['token'],
  'err_message' => $err_message,
  'file_name' => $fileName[0]['icon_filename'],
]);