<?php
session_start();
mb_internal_encoding("utf8");

//未ログイン⇒ログインページへ
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
}

//配列エラーの初期化
$errors = array();

//POST通信 ⇒ エスケープ処理＆バリデーションチェック
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // エスケープ処理
    $input["title"] = htmlentities($_POST["title"] ?? "", ENT_QUOTES);
    $input["comments"] = htmlentities($_POST["comments"] ?? "", ENT_QUOTES);

    // バリデーションチェック⇒
    // タイトル
    if(strlen(trim($input["title"] ?? "")) == 0){
        $errors["title"] = "タイトルを入力してください";
    }
    // コメント
    if(strlen(trim($input["comments"] ?? "")) == 0){
        $errors["comments"] = "コメントを入力してください";
    }

    // エラーが無ければ、DB接続へ
    if(empty($errors)) {
        try{
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;" ,"root" ,""); // DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); //エラーモードを「警告」に設定
            // 投稿内容を格納
            $stmt = $pdo->prepare(" INSERT INTO post(user_id, title, comments) VALUES(?,?,?) "); //SQLクエリ作成（プリペアードステートメント）
            $stmt->execute(array($_SESSION['id'],$input["title"],$input["comments"])); //SQLクエリ実行（プレースホルダに投稿内容をバインド）
            $pdo = NULL; // DB切断
        } catch(PDOException $e) {
            $e->getMessage(); //例外発生時にエラーメッセージを出力
        }
    }
}

//DBに接続し、過去の投稿を取得する
    try {
        $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;" ,"root", "");  // DBに接続
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);   //エラーモードを「警告」に設定

        //過去の投稿を取得
        $stmt_post = $pdo->query("SELECT title, comments, name, posted_at FROM post INNER JOIN user ON post.user_id = user.id ORDER BY posted_at DESC");
        $pdo = null;    // DB切断
    } catch (PDOException $e) {
        $e->getMessage();
    }

//挨拶
$now = new DateTime();
$t =$now->format("G");
if($t >=4 && $t < 12){
    $Greeting = "おはようございます";
}else if($t >=12 && $t < 18){
    $Greeting = "こんにちは";
}else if($t >=18 && $t <= 23){
    $Greeting = "こんばんは";
}else{
    $Greeting = "遅くまでお疲れ様です";
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meate charset="UTF-8">
    <title>4eachblog｜プログラミングに役立つ掲示板（坂本）</title>
    <link rel="stylesheet" type="text/css" href="board.css">

</head>

<body>
    <!-- ヘッダー -->
    <hedder>
        <div class="top">
            <div class="logo">
                <img src="img/4eachblog_logo.jpg">
            </div>
            <div class="user">
                <?php echo $Greeting. $_SESSION['name']. "さん"; ?>
                <form action="logout.php">
                    <input type="submit" class="logout_btm" value="ログアウト">
                </form>
            </div>
        </div>
        <div class="menu">
            <ul>
                <li>トップ</li>
                <li>プロフィール</li>
                <li>4eachについて</li>
                <li>登録フォーム</li>
                <li>問い合わせ</li>
                <li>その他</li>
            </ul>
        </div>
    </hedder>

    <main>
        <!-- コンテンツ -->
        <div class="contents">
            <div class="contents_box1">
                <h1>プログラミングに役立つ掲示板</h1>
            </div>
            <!-- 入力フォーム -->
            <div class="contents_box2">
                <h3 class="inputform">入力フォーム</h3>
                <form name="postform" method="POST" action="">
                    <div class="item">
                        <label>タイトル</label>
                        <input type="text" class="textform" name="title">
                        <?php if(!empty($errors["title"])):?>
                            <p class="err_message"><?php echo $errors["title"]; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="item">
                        <label>コメント</label>
                        <textarea name="comments"></textarea>
                        <?php if(!empty($errors["comments"])):?>
                            <p class="err_message"><?php echo $errors["comments"]; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="item">
                        <input type="submit" class="input_btm" value="送信する">
                    </div>
                </form>
            </div>

            <!-- BBS -->
            <?php foreach($stmt_post as $p) : ?>
                <div class="contents_box2">
                    <h3 class="title"><?php echo $p["title"]; ?></h3>
                    <p class="comments"><?php echo $p["comments"]; ?></p>
                    <p class="post">投稿者: <?php echo $p["name"]; ?></p>
                    <p class="post">投稿時間: 
                        <?php
                            $posted_at = new DateTime($p["posted_at"]);
                            echo $posted_at->format('Y年m月d日 H:i');;
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- サイドバー -->
        <div class="sidebar">
            <div class="sidebar_box">
                人気の記事
            </div>
            <ul>
                <li>PHPオススメ本</li>
                <li>PHP MyAdminの使い方</li>
                <li>今人気のエディタ Top5</li>
                <li>HTMLの基礎</li>
            </ul>
            <div class="sidebar_box">
                オススメリンク
            </div>
            <ul>
                <li>インターノウス株式会社</li>
                <li>XAMPPのダウンロード</li>
                <li>Eclipseのダウンロード</li>
                <li>Bracketsのダウンロード</li>
            </ul>
            <div class="sidebar_box">
                カテゴリ
            </div>
            <ul>
                <li>HTML</li>
                <li>PHP</li>
                <li>MySQL</li>
                <li>JavaScript</li>
            </ul>
        </div>
    </main>

    <!-- フッター -->
    <footer>
        copyright © internous|4each blog the which provides A to Z abaout programming.
    </footer>

</body>

</html>