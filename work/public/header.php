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

$userId = $_SESSION['user_id'];
$fileName = $image->getFileName($userId);

if (isset($_GET['logout'])) {
  $auth->logout();
}

$template = 'header.html.twig';
$template = $twig->load($template);

echo $template->render([
  'token' => $_SESSION['token'],
  'file_name' => $fileName[0]['icon_filename']
]);
