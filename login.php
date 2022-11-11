<?php
session_start();
mb_internal_encoding("utf8");

//ログイン状態であれば、マイボードにリダイレクト
if(isset($_SESSION['id'])){
    header("Location:board.php");
}

// 変数エラーの初期化
$errors = "";

//アクセス方式がPOS ⇒ ユーザー入力処理
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // エスケープ処理（フォームの入力内容：エンティティに変換）
    $input["mail"] = htmlentities($_POST["mail"] ?? "", ENT_QUOTES);
    $input["password"] = htmlentities($_POST["password"] ?? "", ENT_QUOTES);

    // バリデーションチェック
    if(!filter_input(INPUT_POST, "mail",FILTER_VALIDATE_EMAIL)){ //メール欄がメール形式かチェック
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }
    if(strlen(trim($_POST["password"] ?? "")) == 0){ //パスワード欄が入力されているかチェック
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }

    // ログイン認証
    if(empty($errors)){
        // DBに接続 ⇒ ユーザー情報の取得
        try{
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;" ,"root" ,""); // DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); //エラーモードを「警告」に設定
            
            // 入力されたメアドを元にユーザー情報を取り出す
            $stmt = $pdo->prepare("SELECT * FROM user WHERE mail = ? "); //SQLクエリ作成（プリペアードステートメント）
            $stmt->execute(array($input["mail"])); //SQLクエリ実行（プレースホルダにメアドをバインド）
            $user = $stmt->fetch(PDO::FETCH_ASSOC); //カラム名をキーに配列としてテーブル１行取得
            $pdo = NULL; // DB切断
        } catch(PDOException $e) {
            $e->getMessage(); //例外発生時にエラーメッセージを出力
        }
    
        // ユーザー情報取得　＆　パスワード一致　⇒　セッション（＆クッキー）に値を代入、マイボード移動
        if( $user && password_verify($input["password"],$user["password"])){
            //セッションに値を代入
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['password'] = $input["password"];
            // 「ログイン情報を保持する」にチェック ⇒　セッションにセット
            if($_POST['login_keep'] == 1){
                $_SESSION['login_keep'] = $_POST['login_keep'];
            } 
            // 「ログイン情報を保持する」にチェック　⇒　クッキーをセット
            // チェック無し　⇒　クッキー削除
            if(!empty($_SESSION['id']) && !empty($_SESSION['login_keep'])){
                setcookie('mail', $_SESSION['mail'], time()+60*60*24*7);
                setcookie('password', $_SESSION['password'], time()+60*60*24*7);
                setcookie('login_keep', $_SESSION['login_keep'], time()+60*60*24*7);
            } else if(empty($_SESSION['login_keep'])) {
                setcookie('mail','',time()-1);
                setcookie('password','',time()-1);
                setcookie('login_keep','',time()-1);
            }
            header("Location:board.php");
        }else{
            $errors = "メールアドレスとパスワードを正しく入力してください。";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログインページ</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>

<body>
    <h1 class="form_title">ログインページ</h1>
    <form method="POST" action="">
        <div class="item">
            <label>メールアドレス</label>
            <input type="text" class="text" size="35" name="mail" value="<?php
                                                                            if($_COOKIE['login_keep'] ?? '') {
                                                                                echo $_COOKIE['mail'];
                                                                            }
                                                                        ?>">
        </div>

        <div class="item">
            <label>パスワード</label>
            <input type="password" class="text" size="35" name="password" value="<?php
                                                                                    if($_COOKIE['login_keep'] ?? '') {
                                                                                        echo $_COOKIE['password'];
                                                                                    }
                                                                                ?>">
            <?php if (!empty($errors)) : ?>
                <p class="err_message"><?php echo $errors; ?></p>
            <?php endif; ?>
        </div>

        <div class="item">
            <label>
                <input type="checkbox" name="login_keep" value="1"
                <?php
                    if($_COOKIE['login_keep'] ?? ''){
                        echo "checked='checked'"; 
                    }
                ?>>ログイン状態を保持する
            </label>
        </div>

        <div class="item">
            <input type="submit" class="submit" value="ログイン">
        </div>
    </form>
</body>

</html> 