<?php

if(!empty($_SESSION['login_date'])){
  debug('ログイン済みです');

  if($_SESSION['login_date'] + $_SESSION['login_limit'] < time()){
    debug('ログインセッションの有効期限が切れています');

    // セッションを削除
    session_destroy();

    // ログインページに遷移
    header("Location:index.php");
  }else{
    debug('ログインできます');
    $_SESSION['login_date'] = time();
    if(basename($_SERVER['PHP_SELF'] === 'index.php')){
      header("Location:list.php");
    }
  }

}else{
  debug('未ログインです');
  if(basename($_SERVER['PHP_SELF']) !== 'index.php'){
    header("Location:index.php"); //ログインページへ
 }
}