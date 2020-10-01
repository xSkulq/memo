<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('=============================');
debug('logout画面');
debug('=============================');
debugLogStart();

debug('ログアウトします');
// セッションを削除（ログアウトする）
session_destroy();
debug('ログインページに遷移します');
//ログインページへ
header("Location:index.php");