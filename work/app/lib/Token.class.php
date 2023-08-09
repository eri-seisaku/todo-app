<?php

namespace work\app\lib;

class Token
{
  // CSRF対策用関数(Fileの最初にsession_start()と記述する必要がある)
  // フォームで送信機能を実装するならCSRF対策(悪意のある送信対策)が必須
  // トークンを作成しDBに保存されたトークンとフォームのトークンが同じでないと送信できないようにする
  public static function create()
  {
    if (!isset($_SESSION['token'])) {
      // 推測されにくいトークンを作成
      $_SESSION['token'] = bin2hex(random_bytes(32));
    }
  }

  public static function get()
  {
    if (!isset($_SESSION['token'])) {
      return null;
    }
    return $_SESSION['token'];
  }

  public static function validate()
  {
    if (
      empty($_SESSION['token']) ||
      $_SESSION['token'] !== filter_input(INPUT_POST, 'token')
    ) {
      exit('無効な投稿リクエストです');
    }
  }

  // デバック用
  // public static function validate()
  // {
  //   if (
  //     empty($_SESSION['token']) ||
  //     $_SESSION['token'] !== filter_input(INPUT_POST, 'token')
  //   ) {
  //     echo "Session Token: " . $_SESSION['token'] . "<br>";
  //     echo "POST Token: " . filter_input(INPUT_POST, 'token') . "<br>";
  //     exit('無効な投稿リクエストです by Token');
  //   }
  // }
}

