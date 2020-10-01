<?php
require('function.php');

// デバックスタート
debug('=============================');
debug('login画面');
debug('=============================');
debugLogStart();

// ログイン認証
require('auth.php');

if(!empty($_POST)){
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $login_save = (!empty($_POST['login_save'])) ? true : false;

  // バリデーション
  validRequire($email, 'email');
  validRequire($pass, 'pass');

  // email
  validEmail($email, 'email');
  validMaxLen($email, 'email');

  // password
  validMaxLen($pass, 'pass');
  validMinLen($pass, 'pass');
  validStr($pass, 'pass');

  if(empty($err_msg)){
    // db接続
    try{
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM users WHERE email = :email';
      $data = array(':email' => $email);

      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      // クエリの結果を取り出す
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワード一致しました');

        $login_limit = 60 * 60;
        $_SESSION['login_date'] = time();

        // loginのチェックボックス
        if($login_save){
          debug('ログインにチェックをしました');
          // 30日間の有効期限
          $_SESSION['login_limit'] = $login_limit * 24 * 30;
        }else{
          debug('ログインにチェックしてません');
          $_SESSION['login_limit'] = $login_limit;
        }

        // ユーザーidをセッションに格納
        $_SESSION['user_id'] = $result['id'];
        debug('セッション変数の中身：'.print_r($_SESSION,true));
        // 一覧に遷移
        //debug('sesstionのuser_id',$_SESSION['use_id']);
        debug('一覧に遷移します');
        header('Location:list.php');

      }else{
        debug('パスワードが一致しませんでした');
        $err_msg['common'] = MSG09;
      }
    }catch(Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面処理終了^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^');
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
        <h1 class="title">ログイン</h1>
        <form action="" method="post" class="form">
          <div class="box">
            <div>
              <span class="<?php if(!empty($err_msg['common'])) echo 'err';?>"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></span>
            </div>
            <div class="box-label u-mb20">
              <label class="box-label__text">
                Eメール<span class="<?php if(!empty($err_msg['email'])) echo 'err';?>"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span>
                <input type="text" name="email" placeholder="Eメール" class="box-input u-mt5 <?php if(!empty($err_msg['email'])) echo 'box-input__err';?>"
                       value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
              </label>
            </div>
            <div class="box-label">
              <label class="box-label__text">
                パスワード<span class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?></span>
                <input type="password" name="pass" placeholder="パスワード" class="box-input u-mb15 u-mt5 <?php if(!empty($err_msg['pass'])) echo 'box-input__err'; ?>"
                       value="<?php if(!empty($_POST['pass'])) echo $_POST['pass'];?>"> 
              </label>
            </div>
            <div class="auto-login u-mb40">
              <input type="checkbox" name="login_save" class="auto-login__checkbox">
              <span class="u-ml10">自動ログイン</span>
            </div>
            <div>
            <input type="submit" href="" class="button button-long" value="ログイン">
            </div>
          </div>
        </form>
      </section>
    </main>
  </body>
</html>