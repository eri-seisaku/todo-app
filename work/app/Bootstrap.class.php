<?php

namespace work\app;

// 日本の東京の今日の日時を表示する
date_default_timezone_set('Asia/Tokyo');

require_once dirname(__FILE__) . '/../vendor/autoload.php';

class Bootstrap
{
  const DB_HOST = 'db'; // MySQLコンテナのサービス名 (docker-compose.ymlに記載のものと一致させる)
  const DB_NAME = 'todo_db';
  const DB_USER = 'todo_user';
  const DB_PASS = 'todo_pass';
  const DB_TYPE = 'mysql';
  const APP_DIR = '';
  const TEMPLATE_DIR = '/work/public/templates/';
  const CACHE_DIR = false;
  // const APP_URL = 'http://' . $_SERVER['HTTP_HOST'];

  public static $APP_URL;
  public function __construct()
  {
    // APP_URLを初期化
    self::$APP_URL = 'http://' . $_SERVER['HTTP_HOST'];
  }
}

spl_autoload_register(function ($class) {
  $prefix = 'work\\app\\'; // namespaceをwork\appに修正

  if (strpos($class, $prefix) === 0) {
    $classPath = str_replace('\\', '/', substr($class, strlen($prefix)));
    $fileName = __DIR__ . '/' . $classPath . '.class.php';

    if (file_exists($fileName)) {
      require($fileName);
    } else {
      echo 'File not found: ' . $fileName;
    }
  }
});


