<?php
	ini_set('session.gc_maxlifetime', 60 * 24);
	ini_set('session.cookie_lifetime', 60 * 24);
	session_start();
	require_once('common.php');
	// トークンのチェック
	if(empty($_POST['token']) || empty($_SESSION['token'])) {
		$_SESSION = array();
		setcookie(session_name(), '', time() - 1, '/');
		session_destroy();
		header('Location: ./index.php');
		exit();
	}
	if($_POST['token'] === $_SESSION['token']) {
		if(isset($_POST['addUser'])) {
			if(!isset($_POST['mail']) || !isset($_POST['password1']) || !isset($_POST['password2'])) {
				error_log(print_r('Warning loginCheck.php::POSTの失敗\ mail = '.isset($_POST['mail']).' password1 = '.isset($_POST['password1']).' password2 = '.empty($_POST['password2']).' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				exit();
			}
			// ユーザーの登録 パスワードのハッシュ化
			// $db = new MyDB();
			$db = new Login();
			if($db->addUser($_POST['mail'], $_POST['password1'])) {
				echo '登録しました!<br>';
	 		} else {
				echo 'すでに登録されているメールアドレスのようです・・・<br>';
			}
		} else if(isset($_POST['login'])) {
			if(!isset($_POST['mail']) || !isset($_POST['password'])) {
				error_log(print_r('Warning loginCheck.php::POSTの失敗 mail = '.isset($_POST['mail']).' password ='.isset($_POST['password']).' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				exit();
			}
			// ログインチェック パスワードのハッシュ化
			// $db = new MyDB();
			$db = new Login();
			$data = $db->loginCheck($_POST['mail'], $_POST['password']);
			// var_dump($data);
			if(count($data) !== 0) {
				// echo 'ログイン完了<br>';
				$_SESSION = array();
				session_regenerate_id(true);
				$_SESSION['id'] = $data['id'];
				$_SESSION['name'] = $data['name'];
				header('Location: index.php');
				exit();
			} else {
				echo 'ログイン失敗<br>';
				$_SESSION = array();
				setcookie(session_name(), '', time() - 1, '/');
				session_destroy();
			}
		}
	} else {
		echo "もう一度ログインし直してください。<br>";
	}
?>
<br>
<a href="./index.php">トップへ戻る</a>
<br>
<a href="./login.php">ログインページへ</a>
