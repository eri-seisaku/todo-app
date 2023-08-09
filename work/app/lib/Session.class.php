<?php

namespace work\app\lib;

class Session
{
  private $db;

  public function __construct($db)
  {
    if (session_status() === PHP_SESSION_NONE) { // 参考: https://www.softel.co.jp/blogs/tech/archives/6505
      // セッションは有効で、開始していないとき
      session_start();
    }
    $this->db = $db;
    Token::create();
  }

  public function saveLogin($dataArr)
  {
    // ユーザー情報をセッションに保存する
    $_SESSION['res'] = true;
    $_SESSION['user_id'] = $dataArr[0]['id'];
    $_SESSION['email'] = $dataArr[0]['email'];
    $_SESSION['message'] = $dataArr[0]['id'] . 'さん、こんにちは';
  }

  public function saveSignUp($email, $userId)
  {
    // ユーザー情報をセッションに保存する
    $_SESSION['res'] = true;
    $_SESSION['user_id'] = $userId;
    $_SESSION['message'] = $userId . 'さん、ようこそ！';
  }
}