<?php
require('function.php');

// ログイン認証
require('auth.php');

// 商品IDのGETパラメータを取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';

$viewData = getMemoOne($m_id);
debug('取得したDBデータ：'.print_r($viewData,true));

if(!empty($_POST)){

  $concern = $_POST['concern'];
  $thought = $_POST['thought'];

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
        $sql = 'UPDATE memo SET concern = :concern, thought = :thought, update_date = :update_date WHERE user_id = :u_id AND id = :m_id';
        $data = array(':concern' => $concern, ':thought' => $thought,
                      ':update_date' => date('Y-m-d H:i:s'),
                      ':u_id' => $_SESSION['user_id'],
                      ':m_id' => $m_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
          debug('一覧に遷移します');
          header('Location:list.php');
        }else{
          return false;
        }
      }catch(Exception $e){
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

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
                       value="<?php echo (!empty($_POST['concern'])) ? $_POST['concern'] : $viewData['concern'] ?>">
              </label>
            </div>
            <div class="addList-label">
              <label>
                思ったこと<span class="addList-font__small u-ml5">＊144文字以内</span>
                <span class="<?php if(!empty($err_msg['thought'])) echo 'err';?>"><?php if(!empty($err_msg['thought'])) echo $err_msg['thought']; ?></span>
                <!--<input type="text" placeholder="思ったこと" class="addList-input u-mt10 u-mb65">-->
                <textarea name="thought" placeholder="思ったこと" class="addList-textarea u-mt10 u-mb65 <?php if(!empty($err_msg['thought'])) echo 'box-input__err';?>"><?php echo (!empty($_POST['thought'])) ? $_POST['thought'] : $viewData['thought'] ?></textarea>
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