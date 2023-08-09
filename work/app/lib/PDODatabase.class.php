<?php

namespace work\app\lib;

class PDODatabase
{
  private $dbh = null;
  private $db_host = '';
  private $db_user = '';
  private $db_pass = '';
  private $db_name = '';
  private $db_type = '';
  private $order = '';
  private $limit = '';
  private $offset = '';
  private $groupby = '';
  private $join = '';

  public function __construct($db_host, $db_user, $db_pass, $db_name, $db_type)
  {
    $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name, $db_type);
    $this->db_host = $db_host;
    $this->db_user = $db_user;
    $this->db_pass = $db_pass;
    $this->db_name = $db_name;
    // SQL関連
    $this->order = '';
    $this->limit = '';
    $this->offset = '';
    $this->groupby = '';
  }

  private function connectDB($db_host, $db_user, $db_pass, $db_name, $db_type)
  {
    try {
      switch ($db_type) {
        case 'mysql':
          $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
          $dbh = new \PDO($dsn, $db_user, $db_pass, 
          [
            // 例外処理(エラーの原因の特定や処置を行いやすくする)
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            // \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            // true にすると prepare とすることでプレースホルダの値を置き換えてクエリを作成してくれます。false にすると prepare の段階では値の置き換えが発生せず、DB 側で値を置き換えてくれる
            \PDO::ATTR_EMULATE_PREPARES => false,
          ]);
          $dbh->query('SET NAMES utf8');
          break;

        case 'pgsql':
          $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . ' port=5432';
          $dbh = new \PDO($dsn, $db_user, $db_pass);
          break;
      }
    } catch (\PDOException $e) {
      var_dump($e->getMessage());
      exit();
    }

    return $dbh; // 接続情報が入る
  }

  // sql文作成
  // $type = sql文のタイプ
  // $table = テーブル名
  // $columnKey = カラム名
  // $columnVal = カラムの値
  private function getSql($type, $table, $columnKey = '', $columnVal = '', $where = '', $join = '')
  {
    switch ($type) {
      case 'insert':
        $columnKey = "(" . $columnKey . ")";
        $columnVal = "(" . $columnVal . ")";
        $sql = 'INSERT INTO ' . $table . $columnKey . ' VALUES ' . $columnVal;
        break;

      case 'select':
        $columnKey = ($columnKey !== '') ? $columnKey : '*';
        $sql = 'SELECT ' . $columnKey . ' FROM ' . $table . $join;
        break;

      case 'count':
        $columnKey = 'COUNT(*) AS NUM ';
        $sql = 'SELECT ' . $columnKey . ' FROM ' . $table . $join;
        break;

      case 'update':
        $sql = 'UPDATE ' . $table . ' SET ' . $columnVal;
        break;

      case 'delete':
        $sql = 'DELETE FROM ' . $table;
        break;

      default:
        break;
    }

    $whereSQL = ($where !== '') ? ' WHERE  ' . $where : '';
    $other = $this->groupby . "  " . $this->order . "  " . $this->limit . "  " . $this->offset . $this->join;

    // $sql に他の文字列変数 $whereSQL と $other を連結
    $sql .= $whereSQL . $other;

    // echo "WHERE文: " . $whereSQL . "\n";

    return $sql;
  }

  // INSERT文
  public function insert($table, $columnKey, $columnVal, $arrVal = [])
  {
    $sql = $this->getSql('insert', $table, $columnKey, $columnVal);
    // Log関連：ログに書き込む
    $this->sqlLogInfo($sql);

    // SQLクエリ実行(queryかexecute)
    if (!$arrVal) { // 修正：空の場合に直接結果を返す
      $stmt = $this->dbh->query($sql);
      $res = ($stmt !== false) ? $stmt : false;
    } else {
      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($arrVal);
    }

    // エラー対応
    if ($res === false) {
      $this->catchError($stmt->errorInfo());
    }

    return $res; // true or false
  }

  // SELECT文
  public function select($table, $columnKey = '', $where = '', $arrVal = [], $join = '')
  {
    $sql = $this->getSql('select', $table, $columnKey, '', $where, $join);

    // Log関連：ログに書き込む
    $this->sqlLogInfo($sql, $arrVal);

    if (!$arrVal) { // 修正前empty($arrVal)
      $stmt = $this->dbh->query($sql);
      $res = ($stmt !== false) ? $stmt : false;
    } else {
      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($arrVal);
    }
    // エラー対応
    if ($res === false) {
      $this->catchError($stmt->errorInfo());
    }

    // クエリの結果を配列に変換←だと内部結合の時にエラーになるためコメントアウト
    // $data = [];
    // if (!$arrVal) {
    //   $data = $res;
    // } else {
    //   while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    //     array_push($data, $result);
    //   }
    // }

    // クエリの結果を配列に変換
    $data = [];
    if ($stmt instanceof \PDOStatement) { // $stmt が PDOStatement オブジェクトであるか確認　参考: https://www.php.net/instanceof
      while ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        array_push($data, $result);
      }
    } else {
      $data = $res; // オブジェクト以外の場合はそのまま $res を代入
    }

    return $data; // array
  }

  // SELECT文(count)
  public function count($table, $where = '', $arrVal = [])
  {
    $sql = $this->getSql('count', $table, '', '', $where);

    $this->sqlLogInfo($sql, $arrVal);
    $stmt = $this->dbh->prepare($sql);

    $res = $stmt->execute($arrVal);

    if ($res === false) {
      $this->catchError($stmt->errorInfo());
    }

    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    // 結果を整数に
    return intval($result['NUM']);
  }

  // UPDATE文
  public function update($table, $value, $where, $arrVal = [])
  {
    $sql = $this->getSql('update', $table, '', $value, $where);

    // SQLクエリ実行(queryかexecute)
    if (!$arrVal) { // 修正前empty($arrVal)
      $stmt = $this->dbh->query($sql);
      $res = ($stmt !== false) ? $stmt : false;
    } else {
      $stmt = $this->dbh->prepare($sql);
      $res = $stmt->execute($arrVal);
    }

    // エラー対応：query()失敗するとfalseを返す
    if ($stmt === false) {
      $errorInfo = $this->dbh->errorInfo();
      $this->catchError($errorInfo);
    }

    $res = ($stmt !== false) ? true : false;

    return $res; // true or false
  }

  // DELETE文
  public function delete($table, $where, $whereArr)
  {
    $sql = $this->getSql('delete', $table, '', '', $where);

    $stmt = $this->dbh->prepare($sql);
    $res = $stmt->execute($whereArr);

    return $res; // true or false
  }

  // ORDER BY ソート
  public function setOrder($order = '')
  {
    if ($order !== '') {
      $this->order = ' ORDER BY ' . $order;
    }
  }

  // INNER JOIN 内部結合
  public function setInnerJoin($join = '')
  {
    if ($join !== '') {
      $this->join = ' INNER JOIN ' . $join;
    }
  }

  // LIMIT/OFFSET 取得件数指定
  public function setLimitOff($limit = '', $offset = '')
  {
    if ($limit !== '') {
      $this->limit = ' LIMIT ' . $limit;
    }
    if ($offset !== '') {
      $this->offset = ' OFFSET ' . $offset;
    }
  }

  // GROUP BY グループ化
  public function setGroupBy($groupby)
  {
    if ($groupby !== '') {
      $this->groupby = ' GROUP BY ' . $groupby;
    }
  }

  // エラー対応
  private function catchError($errArr = [])
  {
    $errMsg = (!empty($errArr[2])) ? $errArr[2] : '';
    die('SQLエラーが発生しました' . $errArr[2]);
  }

  // Log関連
  private function makeLogFile()
  {
    // ディレクトリ作成
    $logDir = dirname(__DIR__) . "/logs";
    if (!file_exists($logDir)) {
      mkdir($logDir, 0777);
    }
    // ファイル作成
    $logPath = $logDir . '/todo.log';
    if (!file_exists($logPath)) {
      touch($logPath);
    }
    return $logPath;
  }

  // $str = $sql
  private function sqlLogInfo($str, $arrVal = [])
  {
    $logPath = $this->makeLogFile();
    $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", $arrVal));
    error_log($logData, 3, $logPath);
  }

  // PDOで最後に登録したデータのIDを取得する
  public function getLastId()
  {
    // 最後に挿入された最新のIDを取得する
    return $this->dbh->lastInsertId();
  }
}