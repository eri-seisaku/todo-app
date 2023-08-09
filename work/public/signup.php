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
$err_message = $_SESSION['err_message'] ?? null;
$validate_message = $_SESSION['validate'] ?? null;
$post_data = $_SESSION['post_data'] ?? null;

if (isset($_SESSION['res']) && $_SESSION['res'] === true || $result === true) {
  unset($_SESSION['err_message'], $_SESSION['validate'], $_SESSION['post_data']);
  header('Location: index.php');
  exit;
}

if ($result === false) {
  header('Location: signup.php');
  exit;
}

// セッションに保存されたエラーメッセージを削除
unset($_SESSION['err_message'], $_SESSION['validate'], $_SESSION['post_data']);

$template = 'signup.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'err_message' => $err_message,
  'validate_message' => $validate_message,
  'post_data' => $post_data
]);
