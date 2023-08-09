# Todoアプリ

## 概要
Todoアプリです。
タスクの追加、編集、削除などの基本的な機能に加えて、ユーザーアカウントを作成してログインすることで、タスクを個人ごとに管理できます。

## 使用技術、開発環境

- フロントエンド
  - HTML
  - CSS
  - Javascript
  - Twig 3.6

- バックエンド
  - PHP 7.3
  - MySQL 8.0
  - Docker

- その他
  - GitHub
  - Bootstrap 5.0.2
  - Font Awesome 6.4.0

## 環境構築手順

### 1. git からクローンする
```
git clone https://github.com/eri-seisaku/todo-app.git
```

### 2. Docker イメージをビルドします
```
docker compose build
```
- dockerのインストール方法はこちらの手順で行いました。

[Dockerを導入しよう [Windows 10版]](https://dotinstall.com/lessons/basic_dockerdesktop_win)

### 3. Docker コンテナを起動します
```
docker-compose up -d
```

### 4. php コンテナへログインします
```
docker-compose exec php /bin/sh
```

### 5. Twig をインストールします
```
composer require twig/twig
```

### 6. db コンテナへログインします
```
docker-compose exec db bash
```

### 7. MySQL へログインします
```
mysql -u todo_user -p todo_db
```

### 8. データテーブル を作成します

work/app/main.sqlの内容をターミナルに貼り付けてください

### 9. URL にアクセスしてご確認ください

- ローカルホストのURL
http://localhost:8888/

- phpMyAdminのURL
http://localhost:4444/

## 機能一覧

- ログイン・ログアウト
- 新規会員登録
- ユーザー毎のTodo一覧
- ユーザー全てのTodo一覧
- Todo投稿機能
- Todo編集機能
- Todo削除機能
- Todo検索機能
- マイページ
- ユーザー編集
  - メールアドレス変更
  - パスワード変更
  - ユーザーアイコン変更
  - 退会

## 画面

### ログインページ

![login.php](/work/public/image/readme/login.png)

### 会員登録ページ

![signup.php](/work/public/image/readme/signup.png)

### ユーザー毎のTodo一覧ページ

![index.php](/work/public/image/readme/index.png)

### Todo投稿ページ

![post.php](/work/public/image/readme/post.png)

### Todo編集ページ

![edit.php](/work/public/image/readme/edit.png)

### マイページ

![profile.php](/work/public/image/readme/profile.png)

### ユーザー全てのTodo一覧ページ

![list.php](/work/public/image/readme/list.png)

## 開発にあたって
- このアプリケーションは、プログラミング学習サービス「ドットインストール」を参考にし、その一部コードとアイディアを利用しています。ただし、独自の機能追加とカスタマイズが行われており、著作権情報とライセンスを遵守しています。
Github公開前に「ドットインストール」にお問い合わせを送り確認しています。
- 参考にしたレッスン[PHPでTodo管理アプリを作ろう クラス編](https://dotinstall.com/lessons/todo_app_class_php)

## 工夫した点、苦労した点
- 「ドットインストール」のコードとの変更点（工夫した点）
  - twigを導入
  - 独自機能追加、ユーザー認証、マイページ、画像アップロード機能
- 苦労した点
  - dockerを利用してみようと安易に始めてみましたがイメージとコンテナの理解や検索で出てくる内容が十人十色で開発前の環境構築で躓きました。
  - セッションの仕組みを理解できず、post送信されたトークンとセッションに保存されたトークンが異なりエラーになりました。
