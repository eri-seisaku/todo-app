<?php

namespace work\app;
use work\app\lib\Validate;
use work\app\lib\Token;

class Auth
{
  private $db;
  private $session;

  public function __construct($db, $session)
  {
    $this->db = $db;
    $this->session = $session;
  }

  public function processPost()
  {
    // 参考: https://www.javadrive.jp/php/sample/keijiban/index3.html
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // POSTでリクエストされたかどうか

      // 過去のデータが残っている場合は削除
      unset($_SESSION['post_data']);

      Token::validate(); // トークンの確認

      $action = filter_input(INPUT_GET, 'action');

      switch ($action) {
        case 'signup':
          $result = $this->signup();
          break;
        case 'login':
          $result = $this->login();
          break;
        case 'logout':
          $result = $this->logout();
          break;
        case 'update':
          $result = $this->update();
          break;
        case 'delete':
          $result = $this->delete();
          break;
        default:
          exit;
      }
      return $result;
    }
  }

  private function login()
  {
    // $email = isset($_POST['email']) ? trim($_POST['email']) : null; ↓同じ意味
    $email = trim(filter_input(INPUT_POST, 'email'));
    $password = trim(filter_input(INPUT_POST, 'password'));

    // 空の場合
    if ($email === '' || $password === '') {
      $_SESSION['err_message'] = 'メールアドレスとパスワードを入力してください。';
      return false;
    }

    $table = 'users';
    $columnKey = 'id, email, password';
    $where = 'email = :email';
    $arrVal = [$email];
    $dataArr = $this->db->select($table, $columnKey, $where, $arrVal);

    // 返ってきた値が空の場合
    if (!$dataArr) {
      $_SESSION['err_message'] = '入力されたメールアドレスは登録されていません。';
      return false;
    } elseif (password_verify($password, $dataArr[0]['password'])) { // password_verify(パスワード,ハッシュ値)パスワードがハッシュにマッチするかどうかを調べる
      // 成功した場合
      $this->session->saveLogin($dataArr);
      return true;
    } else {
      $_SESSION['err_message'] = 'ログインに失敗しました。';
      return false;
    }
  }

  private function signup()
  {
    $email = trim(filter_input(INPUT_POST, 'email'));
    $password = trim(filter_input(INPUT_POST, 'password'));
    $confirmPassword = trim(filter_input(INPUT_POST, 'confirm_password'));

    // 空の場合
    if ($email === '' || $password === '' || $confirmPassword === '') {
      $_SESSION['err_message'] = 'メールアドレスとパスワードを入力してください。';
      return false;
    }

    // 入力内容の確認
    $errors = $this->confirm($email, $password, $confirmPassword);

    if ($errors !== true) {
      return false;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $table = 'users';
    $columnKey = 'email, password';
    $columnVal = ':email, :password';
    $arrVal = [$email, $password_hash];
    $res = $this->db->insert($table, $columnKey, $columnVal, $arrVal);

    // 返ってきた値がtrueの場合
    if ($res) {
      $userId = $this->db->getLastId();
      $this->session->saveSignup($email, $userId);
      return true;

    } else {
      $_SESSION['err_message'] = 'ユーザー登録に失敗しました。';
      return false;
    }
  }

  private function checkEmailDuplication($email, $userId = null)
  {
    if ($userId !== null) {
        // 特定のIDを除外する場合
        $count = $this->db->count('users', 'email = :email AND id != :userId', [
            'email' => $email,
            'userId' => $userId,
        ]);
    } else {
        // 特定のIDを除外しない場合
        $count = $this->db->count('users', 'email = :email', ['email' => $email]);
    }
    if ($count > 0) {
      $_SESSION['err_message'] = 'このメールアドレスは既に利用されているため、登録できません';
      return false;
    }
    return true;
  }

  public function logout()
  {
    session_destroy();
  }

  public function getProfile($userId)
  {
    $table = 'users';
    $where = 'id = :user_id';
    $arrVal = [$userId];
    $profile = $this->db->select($table, '', $where, $arrVal);

    return $profile;
  }

  private function update()
  {
    $email = filter_input(INPUT_POST, 'email');
    $newPassword = trim(filter_input(INPUT_POST, 'new_password'));
    $confirmPassword = trim(filter_input(INPUT_POST, 'confirm_password'));

    $errors = [];

    if (empty($email)) {
      $_SESSION['err_message'] = 'メールアドレスを入力してください。';
      return false;
    } else {
      $errors = $this->confirmEmail($email, $_SESSION['user_id']);
    }
    if (!empty($newPassword)) {
      $errors = $this->confirmPassword($newPassword, $confirmPassword);
    } else {
      // 現在のユーザー情報を取得
      $currentProfile = $this->getProfile($_SESSION['user_id']);

      // var_dump($currentProfile[0]['password']);
      $password = $currentProfile[0]['password'];

      if (empty($password)) {
        $_SESSION['err_message'] = '現在のパスワードの取得に失敗しました。';
        return false;
      }
    }

    if ($errors !== true) {
      return false;
    }

    $res = $this->updateProfile($_SESSION['user_id'], $email, $newPassword);
    return $res; // true or false
  }

  private function updateProfile($userId, $email = '', $newPassword = '')
  {
    $password_hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $table = 'users';
    $value = 'email = :email, password = :password';
    $where = 'id = :user_id';
    $arrVal = ['email' => $email, ':password' => $password_hash, ':user_id' => $userId];
    $res = $this->db->update($table, $value, $where, $arrVal);
    return $res;
  }

  private function delete()
  {
    $id = $_SESSION['user_id'];
    if (empty($id)) {
      return false;
    }
    $table = 'users';
    $where = 'id = :id';
    $whereArr = [':id' => $id];
    $res = $this->db->delete($table, $where, $whereArr);

    // セッションデータを完全に削除する
    $_SESSION = array();

    return $res;
  }


  // 登録、更新前の確認 ---------------------------------------
  // signup用
  private function confirm($email, $password, $confirmPassword)
  {
    // バリデーションの実行
    $emailErrors = Validate::validate($email, 'email');
    $passwordErrors = Validate::validate($password, 'password');

    // バリデーションエラーメッセージを保存
    $errors = [];
    if (!empty($emailErrors)) {
      $errors = array_merge($errors, $emailErrors);
    }
    if (!empty($passwordErrors)) {
      $errors = array_merge($errors, $passwordErrors);
    }
    if (!empty($errors)) {
      $_SESSION['validate'] = $errors;
      // リダイレクトした時に入力内容を保持
      $_SESSION['post_data'] = [
        'email' => $email,
        // 'name' => $name, // 名前etc
      ];
      return false;
    }

    // パスワード確認のバリデーション
    if ($password !== $confirmPassword) {
      $_SESSION['err_message'] = '新しいパスワードと確認用パスワードが一致しません。';
      // リダイレクトした時に入力内容を保持
      $_SESSION['post_data'] = [
        'email' => $email,
        // 'name' => $name, // 名前etc
      ];
      return false;
    }

    // 重複チェックを実行
    $dupError = $this->checkEmailDuplication($email);

    return $dupError;
  }

  // profile更新用
  private function confirmEmail($email, $userId)
  {
    // バリデーションの実行
    $emailErrors = Validate::validate($email, 'email');

    // バリデーションエラーメッセージを保存
    if (!empty($emailErrors)) {
      $_SESSION['validate'] = $emailErrors;
      // リダイレクトした時に入力内容を保持
      $_SESSION['post_data'] = [
        'email' => $email,
        // 'name' => $name, // 名前etc
      ];
      return false;
    }

    // 重複チェックを実行
    $dupError = $this->checkEmailDuplication($email, $userId);

    return $dupError; // true or false
  }

  private function confirmPassword($password, $confirmPassword)
  {
    // バリデーションの実行
    $passwordErrors = Validate::validate($password, 'password');

    // バリデーションエラーメッセージを保存
    if (!empty($passwordErrors)) {
      $_SESSION['validate'] = $passwordErrors;
      return false;
    }

    // パスワード確認のバリデーション
    if ($password !== $confirmPassword) {
      $_SESSION['err_message'] = '新しいパスワードと確認用パスワードが一致しません。';
      return false;
    }

    return true;
  }

  // 下記は使用していない
  private function toggle()
  {
    $id = $_SESSION['user_id'];
    if ($id === '') {
      return false;
    }

    $table = 'users';
    $value = 'delete_flg = NOT delete_flg';
    $where = 'id = :id';
    $whereArr = [':id' => $id];
    $res = $this->db->update($table, $value, $where, $whereArr);
  }

}
?>