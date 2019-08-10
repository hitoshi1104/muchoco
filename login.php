<?php
	session_start();
	require_once('common.php');
	$ProtectInjection = new ProtectInjection();
	$token = $ProtectInjection->getToken();
	$_SESSION['token'] = $token;
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>けーじばんログイン画面</title>
		<!--link href="style.css" rel="stylesheet" type="text/css"-->
	</head>
	<body>
		<h1>けーじばん@ログイン画面</h1>
		<h2 id="addUser">ユーザー登録</h2>
		<form class="" action="./loginCheck.php" method="post">
			<input type="hidden" name="token" value=<?=$token?>>
			<p>メールアドレスを入力してください。</p>
			<input type="email" name="mail" value="" size="40" required>
			<p>パスワードを入力してください　※8文字以上</p>
			<input type="password" name="password1" value="" pattern="^[0-9A-Za-z]+$" size="30" required>
			<p>もう一度パスワードを入力してください　※8文字以上</p>
			<input type="password" name="password2" value="" pattern="^[0-9A-Za-z]+$" size="30" required>
			<br>
			<button type="submit" name="addUser" onclick="return addUserCheck()">アカウントの作成</button>
		</form>
		<br>
		<h2 id="login">ログイン</h2>
		<form class="" action="./loginCheck.php" method="post">
			<input type="hidden" name="token" value=<?=$token?>>
			<p>メールアドレスを入力してください。</p>
			<input type="email" name="mail" value="" size="40" required>
			<p>パスワードを入力してください</p>
			<input type="password" name="password" value="" pattern="^[0-9A-Za-z]+$" size="30" required>
			<br>
			<button type="submit" name="login">ログイン</button>
		</form>
		<br>
		<a href="./index.php">掲示板トップへ</a>
	</body>
</html>

<script type="text/javascript">
	function addUserCheck() {
		let password1 = document.getElementsByName('password1')[0].value;
		let password2 = document.getElementsByName('password2')[0].value;
		if(password1 !== password2 && password1 !== "" && password2 !== "") {
			alert('入力されたパスワードが一致しません！');
			return false;
 		} else if(password1.length < 8 || password2.length < 8) {
			alert('パスワードは8文字以上にしてください。');
		}
		return true;
	};
</script>
