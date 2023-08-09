<?php

namespace work;

require_once(__DIR__ . '/../app/Bootstrap.class.php');

use work\app\Bootstrap;
use work\app\lib\PDODatabase;
use work\app\lib\Session;
use work\app\Auth;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$session = new Session($db);
$auth = new Auth($db, $session);

$result = $auth->processPost();
$message = $_SESSION['message'] ?? null; // ※
$err_message = $_SESSION['err_message'] ?? null; // ※

// ※は下記の意味
// if (isset($errors['login'])) {
//   $err_message = $errors['login'];
// } else {
//   $err_message = null;
// }

// デバッグ表示
// echo '<pre>';
// var_dump($_POST['token'], $_SESSION['token']);
// var_dump($_SESSION);
// echo '</pre>';

if (isset($_SESSION['res']) && $_SESSION['res'] === true || $result === true) {
  unset($_SESSION['err_message']);
  header('Location: index.php');
  exit;
}

if ($result === false) {
  header('Location: login.php');
  exit;
}

// セッションに保存されたエラーメッセージを削除
unset($_SESSION['message'], $_SESSION['err_message'], $_SESSION['validate'], $_SESSION['post_data']);

$template = 'login.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'message' => $message,
  'err_message' => $err_message,
]);
