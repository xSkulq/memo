<nav class="header">
      <div class="nav">
        <div class="logo">
          <p class="logo-img">気づいたこと</p>
          <!--<img src="" alt="logo" class="logo-img">-->
        </div>
        <ul class="button-right">
          <?php if(empty($_SESSION['user_id'])){?>
            <li><a href="singup.php" class="button-yellow button-block">新規登録</a></li>
            <li><a href="index.php" class="button-block">ログイン</a></li>
          <?php }else{ ?>
            <li><a href="logout.php" class="button-block">ログアウト</a></li>
          <?php } ?>
        </ul>
      </div>
    </nav>