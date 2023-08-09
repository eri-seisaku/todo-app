<?php

namespace work\app\lib;

class Validate
{
  // バリデーションルールを定義
  private static $rules = [
    'email' => '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/',
    'password' => '/^[a-zA-Z0-9.?\/-]{8,16}$/',
    'type' => '/^image\/(jpeg|png)$/i',
    'size' => 1048576,
    'name' => 30
  ];

  private static $errorMessages = [
    'email' => [
      'required' => 'メールアドレスを入力してください。',
      'format' => '有効なメールアドレスの形式で入力してください。',
    ],
    'password' => [
      'required' => 'パスワードを入力してください。',
      'format' => 'パスワードは8文字以上16文字以下の半角英数字、記号.?/-のみが使用可能です。',
    ],
    'image' => [
      'size' => 'アップロードできる画像のサイズは、1MBまでです。',
      'type' => 'JPEG形式(jpg/jpe/jpeg)とPNG形式(png)以外はアップロードできません。',
      'name' => 'ファイル名は拡張子を含めて30文字以下にしてください。',
    ],
  ];

  public static function validate($input, $rule)
  {
    $errArr = [];

    if (empty($input)) {
      $errArr[$rule] = self::$errorMessages[$rule]['required'];
    } elseif (!preg_match(self::$rules[$rule], $input)) {
      $errArr[$rule] = self::$errorMessages[$rule]['format'];
    }

    return $errArr;
  }

  // rule = size
  public static function validateImgSize($fileSize, $rule)
  {
    if ($fileSize > self::$rules[$rule]) {
      return self::$errorMessages['image'][$rule];
    }
  }

  // rule = type
  public static function validateImgType($fileType, $rule)
  {
    if (!preg_match(self::$rules[$rule], $fileType)) {
      return self::$errorMessages['image'][$rule];
    }
  }

  // rule = name
  public static function validateImgName($fileName, $rule)
  {
    if (mb_strlen($fileName) > self::$rules[$rule]) {
      return self::$errorMessages['image'][$rule];
    }
  }

}
