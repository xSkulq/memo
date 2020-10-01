<?php

require('function.php');
// デバックスタート
debug('=============================');
debug('addList画面');
debug('=============================');
debugLogStart();

// ログイン認証
require('auth.php');

if(!empty($_POST)){
  $concern = $_POST['concern'];
  $thought = $_POST['thought'];

  // セッションにthoughtの値を保持
  $_SESSION['comment'] = $thought;

  // バリデーションチェック
  validRequire($concern, 'concern');
  validRequire($thought, 'thought');

  if(empty($err_msg)){

    // 気づいたこと
    validConcern($concern, 'concern');
    // 思ったこと
    validThiught($thought, 'thought');

    if(empty($err_msg)){
      try{
        //db接続
        $dbh = dbConnect();
        $sql = 'INSERT INTO memo (concern,thought,user_id,create_date) VALUES(:concern,:thought,:u_id,:create_date)';
        $data = array(':concern' => $concern, ':thought' => $thought, ':u_id' => $_SESSION['user_id'],
                      ':create_date' => date('Y-m-d H:i:s'));

        // クエリ実行 
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功時
        if($stmt){
          // メモのid
          $_SESSION['memo'] = $dbh->lastInsertId();
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

debug('画面処理終了^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^');
?>

<!-- head -->
<?php
require('head.php');
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
    </nav>

    <!-- contents -->
    <main class="main">
      <section class="content">
        <form class="form u-mt150" action="" method="post">
          <div class="addList-content">
            <div class="addList-label">
              <label>
                気づいたこと<span class="addList-font__small u-ml5">＊50文字以内</span>
                <span class="<?php if(!empty($err_msg['concern'])) echo 'err';?>"><?php if(!empty($err_msg['concern'])) echo $err_msg['concern']; ?></span>
                <input type="text" name="concern" placeholder="気づいたこと" class="addList-input u-mt10 u-mb70 <?php if(!empty($err_msg['concern'])) echo 'box-input__err';?>"
                       value="<?php if(!empty($_POST['concern'])) echo $_POST['concern']; ?>">
              </label>
            </div>
            <div class="addList-label">
              <label>
                思ったこと<span class="addList-font__small u-ml5">＊144文字以内</span>
                <span class="<?php if(!empty($err_msg['thought'])) echo 'err';?>"><?php if(!empty($err_msg['thought'])) echo $err_msg['thought']; ?></span>
                <!--<input type="text" placeholder="思ったこと" class="addList-input u-mt10 u-mb65">-->
                <textarea name="thought" placeholder="思ったこと" class="addList-textarea u-mt10 u-mb65 <?php if(!empty($err_msg['thought'])) echo 'box-input__err';?>"><?php (empty($thought)) ? '' : print($thought); ?></textarea>
              </label>
            </div>
            <div>
              <input type="submit" href="" class="button button-long" value="保存">
            </div>
          </div>
        </form>
      </section>
    </main>
  </body>
</html>