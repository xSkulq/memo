<?php

require('function.php');

// デバックスタート
debug('=============================');
debug('singup画面');
debug('=============================');
debugLogStart();

if(!empty($_POST)){
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // バリデーションチェック
  validRequire($email, 'email');
  validRequire($pass, 'pass');
  validRequire($pass_re, 'pass_re');

  debug('未入力のバリデーションクリア');

  if(empty($err_msg)){

    // email
    validEmail($email, 'email');
    validMaxLen($email, 'email');
    validEmailDup($email);
    debug('emailのバリデーションクリア');

    // password
    validMaxLen($pass, 'pass');
    validMinLen($pass, 'pass');
    validStr($pass, 'pass');
    debug('passのvalidをクリア');

    if(empty($err_msg)){
      //pass_re
      validMatch($pass, $pass_re, 'pass_re');
      debug('pass_reのvalidをクリア');
    }

    if(empty($err_msg)){
      try{
        //db接続
        $dbh = dbConnect();
        $sql = 'INSERT INTO users (email,password,create_date,login_time) VALUES(:email,:password,:create_date,:login_time)';
        $data = array(':email' => $email, ':password' => password_hash($pass, PASSWORD_DEFAULT),
                      ':create_date' => date('Y-m-d H:i:s'),
                      ':login_time' => date('Y-m-d H:i:s'));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功時
        if($stmt){
          //ログイン有効期限（デフォルトを１時間とする）
          $sesLimit = 60*60;
          // 最終ログイン日時を現在日時に
          $_SESSION['login_date'] = time();
          $_SESSION['login_limit'] = $sesLimit;
          // ユーザーIDを格納
          $_SESSION['user_id'] = $dbh->lastInsertId();

          debug('セッション変数の中身：'.print_r($_SESSION,true));

          header("Location:list.php"); //マイページへ
      }
      }catch(Exception $e){
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
debug('画面処理終了^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^');
?>

<!-- head -->
<?php
require('head.php');
?>

  <body>

    <!-- header -->
    <?php
      require('header.php');
    ?>

    <!-- contents -->
    <main class="main">
      <section class="content">
        <h1 class="title">新規登録</h1>
        <form action="" method="post" class="form">
          <div class="box">
            <div class="box-label u-mb20">
              <label class="box-label__text">
                Eメール<span class="<?php if(!empty($err_msg['email'])) echo 'err';?>"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                <input type="text" name="email" placeholder="Eメール" class="box-input u-mt5 <?php if(!empty($err_msg['email'])) echo 'box-input__err';?>"
                       value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
              </label>
            </div>
            <div class="box-label u-mb20">
              <label class="box-label__text">
                パスワード<span class="box-label__text-small u-ml5">*英数字6文字以上</span><span class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?></span>
                <input type="password" name="pass" placeholder="パスワード" class="box-input u-mt5 <?php if(!empty($err_msg['pass'])) echo 'box-input__err'; ?>"
                       value="<?php if(!empty($_POST['pass'])) echo $_POST['pass'];?>">
              </label>
            </div>
            <div class="box-label u-mb40">
              <label class="box-label__text">
                確認<span class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>"><?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?></span>
                <input type="password" name="pass_re" placeholder="確認パスワード" class="box-input u-mt5 <?php if(!empty($err_msg['pass_re'])) echo 'box-input__err';?>">
              </label>
            </div>
            <div>
              <input type="submit" href="" class="button button-long" value="登録">
            </div>
          </div>
        </form>
      </section>
    </main>

  </body>
</html>