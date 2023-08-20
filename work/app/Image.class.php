<?php

namespace work\app;
use work\app\lib\Validate;
use work\app\lib\Token;

class Image
{
  private $db;
  private $session;

  public function __construct($db, $session)
  {
    $this->db = $db;
    $this->session = $session;
  }

  public function upload($imageFile)
  {
    Token::validate(); // トークンの確認

    $fileSize = $imageFile['size'];  // ファイルサイズ
    $fileName = $imageFile['name']; // ファイル名
    $fileInfo = getimagesize($imageFile['tmp_name']);
    $fileType = $fileInfo['mime']; // ファイルタイプ

    if (empty($fileSize)) {
        return false;
    }

    $fileSizeErr = Validate::validateImgSize($fileSize, 'size');
    $fileTypeErr = Validate::validateImgType($fileType, 'type');
    $fileNameErr = Validate::validateImgName($fileName, 'name');

    // エラーメッセージを連想配列として取得
    $fileErrors = compact('fileSizeErr', 'fileTypeErr', 'fileNameErr');

    // 空でないエラーメッセージのみをセッションに保存
    $_SESSION['validate'] = array_filter($fileErrors);

    if (!empty($fileSizeErr) || !empty($fileTypeErr) || !empty($fileNameErr)) {
      return false;
    }
    // ファイルが一時的に保存された場所から移動させる
    $destination = dirname(__DIR__) . '/public/image/icon/' . $fileName;


    // 保存先のディレクトリが存在しない場合は作成する
    if (!is_dir(__DIR__ . '/../../public/image/icon/')) {
      mkdir(__DIR__ . '/../../public/image/icon/', 0777, true);
    }

    if (move_uploaded_file($imageFile['tmp_name'], $destination)) {
      $userId = $_SESSION['user_id'];
      $result = $this->update($fileName, $userId);

      if ($result) {
        $_SESSION['message'] =  "画像のアップロードに成功しました。";
        // var_dump($destination);
        return true;
      } else {
        $_SESSION['err_message'] = '画像のアップロードに失敗しました。';
        return false;
      }
    }
  }


  public function update($fileName, $userId)
  {
    $table = 'users';
    $value = 'icon_filename = :icon_filename';
    $where = 'id = :user_id';
    $arrVal = [':icon_filename' => $fileName, ':user_id' => $userId];
    $res = $this->db->update($table, $value, $where, $arrVal);
    return $res;
  }

  // public function update($fileName, $userId)
  // {
  //   $table = 'users';
  //   $columnKey = 'icon_filename';
  //   $columnVal = ':icon_filename';
  //   $arrVal = [':icon_filename' => $fileName];
  //   $res = $this->db->insert($table, $columnKey, $columnVal, $arrVal);
  //   return $res;
  // }

  public function getFileName($userId)
  {
    $table = 'users';
    $columnKey = 'icon_filename';
    $where = 'id = :user_id';
    $arrVal = [$userId];
    $path = $this->db->select($table, $columnKey, $where, $arrVal);

    return $path;
  }

}
