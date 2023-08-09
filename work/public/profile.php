<?php

namespace work;

require_once(__DIR__ . '/../app/Bootstrap.class.php');

use work\app\Bootstrap;
use work\app\lib\PDODatabase;
use work\app\lib\Session;
use work\app\Auth;
use work\app\Image;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig\Loader\FilesystemLoader(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig\Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$session = new Session($db);
$auth = new Auth($db, $session);
$image = new Image($db, $session);

// セッションが存在しない場合はlogin.phpを読み込む
if (!isset($_SESSION['res']) || $_SESSION['res'] === false) {
  header('Location: login.php');
  exit;
}

// ユーザープロフィール情報の取得
$userId = $_SESSION['user_id'];
$profileInfo = $auth->getProfile($userId);
$fileName = $image->getFileName($userId);
// var_dump($fileName[0]['icon_filename']); // デバック

// 画像がアップロードされたら
if (isset($_FILES['image'])) {
  // var_dump($_FILES);
  $image->upload($_FILES['image']);
  header('Location: profile.php');
  exit;
}

// var_dump($_POST);
// var_dump($_FILES);

// データの更新があったら
if (isset($_GET['action'])) {
  if ($_GET['action'] === 'update') {
    $result = $auth->processPost();
    $_SESSION['message'] = $result ? '更新しました。' : '';
    header('Location: profile.php');
    exit;
  } elseif ($_GET['action'] === 'delete') {
    $result = $auth->processPost();
    if ($result === true) {
      $_SESSION['message'] = 'アカウントの削除に成功しました。';
      header('Location: login.php');
    } else {
      $_SESSION['err_message'] = 'アカウントの削除に失敗しました。';
      header('Location: profile.php');
    }
    exit;
  }
}

// 各メッセージの取得
$message = $_SESSION['message'] ?? null;
$err_message = $_SESSION['err_message'] ?? null;
$validate_message = $_SESSION['validate'] ?? null;

unset($_SESSION['message'], $_SESSION['err_message'], $_SESSION['validate'],);

$template = 'profile.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'email' => $profileInfo[0]['email'],
  'message' => $message,
  'err_message' => $err_message,
  'validate_message' => $validate_message,
  'file_name' => $fileName[0]['icon_filename']
]);
