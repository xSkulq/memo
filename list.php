<?php

require('function.php');
// デバックスタート
debug('=============================');
debug('list画面');
debug('=============================');
debugLogStart();

// ログイン認証
//require('auth.php');

// 画面表示用データ取得
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ

// 表示件数
$listSpan = 10;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); 

$seach = (!empty($_GET['seach'])) ? $_GET['seach'] : '';

$dbMemoData = getMemoList($currentMinNum, $seach);


if($_POST){
  $destory = $_POST['destory'];
  debug('POST送信されました');
  if($destory){
    try{
      //db接続
      $dbh = dbConnect();
      $sql = 'UPDATE memo SET is_delete = 1 WHERE id = :m_id';
      $data = array(':m_id' => $_SESSION['memo']);
  
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      if($stmt){
        debug('メモを削除しました');
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
        <div class="list">
          <h1 class="list-title">一覧</h1>
          <form action="">
          <div class="seach">
            <input type="text" placeholder="検索" class="list-input" name="seach" value="<?php echo $seach?>">
            <input type="submit" class="button-seach" value="検索">
          </div>
          </form>
          <div class="list-middle u-mb20">
            <div class="list-text">
              <p><span class="list-text__size"><?php echo (!empty($dbMemoData['data'])) ? $currentMinNum+1 : 0; ?></span>-<span class="num"><?php echo $currentMinNum+count($dbMemoData['data'])?>件</p>
              <p><span class="u-ml20">全</span><span class="list-text__size u-ml5"><?php print($dbMemoData['total']) ?></span>件</p>
            </div>
            <div>
              <a href="addList.php" class="button button-middle">追加</a>
            </div>
          </div>


            <div class="u-mb160">
            <?php foreach((array)$dbMemoData['data'] as $key => $val): ?>

              <div class="list-box">
                <div class="list-box__text u-mb5 js-concern">
                  <p class="u-mb5"><?php print($val['concern'])?></p>
                </div>
                <div class="u-mb10">
                  <p><?php print($val['thought']) ?></p>
                </div>
                <div class="list-box__end">
                  <p><?php echo date('Y/m/d', strtotime($val['create_date']))?></p>
                  <div class="u-flex">
                    <div>
                      <a href="edit.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&m_id='.$val['id'] : '?m_id='.$val['id']; ?>" class="button-bigSmall u-ml25">編集</a>
                    </div>
                    <form action="" method="post">
                      <!--<a href="" class="button-bigSmall u-ml5">削除</a>-->
                      <input type="submit" name="destory" href="" class="button-destory u-ml5" value="削除">
                    </form>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
        </div>
      </section>

      <!-- pagination -->
      <?php pagination($currentPageNum, $dbMemoData['total_page']); ?>
    </main>
  </body>
</html>