<?php

namespace work\app;
use work\app\lib\Token;

$bootstrap = new Bootstrap;

class Todo
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      Token::validate();

      $action = filter_input(INPUT_GET, 'action');

      switch ($action) {
        case 'add':
          $result = $this->add();
          break;
        case 'toggle':
          $result = $this->toggle();
          break;
        case 'delete':
          $result = $this->delete();
          break;
        case 'clear':
          $result = $this->clear();
          break;
        case 'update':
          $result = $this->update();
          break;
        default:
          exit;
      }
      return $result;
    }
  }

  private function add()
  {
    $title = trim(filter_input(INPUT_POST, 'title'));
    $content = trim(filter_input(INPUT_POST, 'content'));
    $user_id = $_POST['user_id'];

    if ($title === '' || $content === '') {
      $_SESSION['err_message'] = 'タイトル又はコンテンツを入力してください。';
      return false;
    }

    $table = ' todos ';
    $columnKey = 'title, content, user_id'; // ユーザーIDを追加
    $columnVal = ':title, :content, :user_id';
    $arrVal = [$title, $content, $user_id];
    $res = $this->db->insert($table, $columnKey, $columnVal, $arrVal);

    if ($res) {
      $_SESSION['message'] = 'Todo追加に成功しました。';
      return true;
    } else {
      $_SESSION['err_message'] = 'Todo追加に失敗しました。';
      return false;
    }
  }

  private function toggle()
  {
    $id = filter_input(INPUT_POST, 'id');
    if ($id === '') {
      return;
    }

    // デバッグ表示
    // var_dump($_POST['token'], $_SESSION['token']);

    $table = 'todos';
    $value = 'is_done = NOT is_done';
    $where = 'id = :id';
    $whereArr = [':id' => $id];
    $res = $this->db->update($table, $value, $where, $whereArr);
  }

  private function delete()
  {
    $id = filter_input(INPUT_POST, 'id');
    if ($id === '') {
      return;
    }

    $table = 'todos';
    $where = 'id = :id';
    $whereArr = [':id' => $id];
    $res = $this->db->delete($table, $where, $whereArr);
  }

  private function clear()
  {
    $table = 'todos';
    $where = 'is_done = 1';
    $whereArr = [];
    $res = $this->db->delete($table, $where, $whereArr);
  }


  public function getAll()
  {
    $table = 'todos';
    $order = 'id DESC';
    $this->db->setOrder($order);
    $allTodo = $this->db->select($table);
    return $allTodo;
  }

  public function getUser($userId)
  {
    $table = 'todos';
    $where = 'user_id = :user_id';
    $arrVal = [$userId];
    $order = 'id DESC';
    $this->db->setOrder($order);
    $userTodo = $this->db->select($table,'', $where, $arrVal);
    return $userTodo;
  }

  public function update()
  {
    $id = filter_input(INPUT_POST, 'id');
    $title = trim(filter_input(INPUT_POST, 'title'));
    $content = trim(filter_input(INPUT_POST, 'content'));

    if ($title === '' && $content === '' && $id === '') {
      return false;
    }
    $table = 'todos';
    $value = 'title = :title, content = :content';
    $where = 'id = :id';
    $arrVal = [':title' => $title, ':content' => $content, ':id' => $id];
    $res = $this->db->update($table, $value, $where, $arrVal);

    return $res; // 更新結果（true or false）を返す
  }

  public function getFileAndTodo()
  {
    // https://magazine.techacademy.jp/magazine/5255
    // https://www.javadrive.jp/mysql/join/index1.html
    // SELECT todos.*, users.id, users.icon_filename
    // FROM todos
    // INNER JOIN users ON todos.user_id = users.id;
    $table = "todos";
    $columnKey = "todos.*, users.id, users.icon_filename";
    $join = 'users ON todos.user_id = users.id';

    // DB オブジェクトの inner join 設定
    $this->db->setInnerJoin($join);

    $listArr = $this->db->select($table, $columnKey);
    
    // INNER JOIN をリセット
    // $this->db->setInnerJoin('');
    
    return $listArr;
  }

}
?>