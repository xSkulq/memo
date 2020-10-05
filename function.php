<?php
//================================
// ログ
//================================
//ini_set('display_errors',1);
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

//================================
// セッション準備・セッション有効期限を延ばす
//================================
//セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime ', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//================================
// 画面表示処理開始ログ吐き出し関数
//================================
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug( 'ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit'] ) );
  }
}

// ===============================
// 定数
// ===============================
define('MSG01', '入力されていません');
define('MSG02', 'email形式ではありません');
define('MSG03', '6文字以上で入力してください');
define('MSG04', '255文字以内で入力してください');
define('MSG05', '入力されたパスワードの文字と違います');
define('MSG06', 'emailが重複しています');
define('MSG07', '英数字で入力してください');
define('MSG08', 'エラーが発生しました。しばらくお待ちください');
define('MSG09', 'emailまたはパスワードの入力が間違えています');
define('MSG10', '50文字以内で入力してください');
define('MSG11', '144文字以内で入力してください');

// ================================
// err_msgの配列
// ================================
$err_msg = array();

// =================================
// バリデーションチェック
// =================================

// 入力確認チェック
function validRequire($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = MSG01;
    debug($err_msg[$key]);
  }
}

// Email形式チェック
function validEmail($str, $key){
  $filter = filter_var($str, FILTER_VALIDATE_EMAIL);// 下手な正規表現だとセキュリティー上よくないらしいのでfilter_varで簡易的にチェックをする
  if(empty($filter)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

// Email重複チェック
function validEmailDup($email){
  global $err_msg;
  try{
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email';
    $data = array(':email' => $email);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリの結果を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    debug($result);

    if(!empty(array_shift($result))){ // array_shiftで$resultの先頭の値を取得
      $err_msg['email'] = MSG06;
    }
  }catch(Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// 最小文字数チェック
function validMinLen($str, $key, $min = 6){
  global $err_msg;
  if(mb_strlen($str) < $min){
    $err_msg[$key] = MSG03;
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 255){
  global $err_msg;
  if(mb_strlen($str) > $max){
    $err_msg[$key] = MSG04;
  }
}

// 半角英数字チェック
function validStr($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG07;
  }
}

// パスワードと確認が一緒かどうかのチェック
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// 50文字以内かチェック
function validConcern($str, $key){
  if(mb_strlen($str) > 50){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}

// 144文字以内かチェック
function validThiught($str, $key){
  if(mb_strlen($str) > 144){
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

//================================
// データベース
//================================
//DB接続関数
function dbConnect(){
  //DBへの接続準備
  $db = parse_url($_SERVER['CLEARDB_DATABASE_URL']);
  $db['dbname'] = ltrim($db['path'], '/');
  $dsn = "mysql:dbname={$db['dbname']};host={$db['host']};charset=utf8";
  // ローカル時に使っていた
  //$dsn = 'mysql:dbname=memo;host=localhost;charset=utf8';
  //$user = 'root';
  //$password = 'root';
  $user = $db['user'];
  $password = $db['pass'];
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,//_SILENT
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data){
  global $err_msg;
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG08;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}

// メモ情報の取得
function getMemoList($currentMinNum = 1,$seach,$span = 10){
  global $err_msg;
  try{
    //db接続
    $dbh = dbConnect();
    // メモのidの数
    $sql = 'SELECT id FROM memo WHERE user_id = :u_id AND is_delete = 0';
    $data = array(':u_id' => $_SESSION['user_id']);

    $stmt = queryPost($dbh, $sql, $data);
    $memo['total'] = $stmt->rowCount();
    $memo['total_page'] = ceil($memo['total']/$span);

    // メモ情報
    $sql = 'SELECT * FROM memo WHERE user_id = :u_id AND is_delete = 0';
    if(!empty($seach)) $sql .= " AND concern LIKE '%" .$seach . "%'";
    $sql .= ' ORDER BY id DESC';
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array(':u_id' => $_SESSION['user_id']);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);


    if($stmt){
      // クエリ結果全てを格納
      $memo['data'] = $stmt->fetchAll();
      return $memo;
    }
  }catch(Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// メモ1つの情報
function getMemoOne($m_id){
  global $err_msg;
  //db接続
  try{
    $dbh = dbConnect();
    $sql = 'SELECT concern,thought,id,update_date FROM memo WHERE id = :m_id AND user_id = :u_id';
    $data = array(':m_id' => $m_id, ':u_id' => $_SESSION['user_id']);

    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}



//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  
  echo '<div class="pagination">';
    echo '<ul class="pagination-block">';
      if($currentPageNum != 1){
        echo '<li class="margin-auto"><a class="pagination-icon" href="?p=1'.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="margin-auto ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a class="pagination-number" href="?p='.$i.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="margin-auto"><a class="pagination-icon" href="?p='.$maxPageNum.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key, $arr_del_key,true)){
        $str .= $key. '='.$val. '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

?>