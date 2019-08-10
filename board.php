<?php
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
		// url からスレッドを立てたのかコメントなのかを調べる
		$check = explode('/', $_SERVER['HTTP_REFERER']);
		$check = $check[count($check) - 1];
		// テキストの投稿
		if(isset($_POST['submitSentence'])) {
			$postData['poster'] = empty($_SESSION['id']) ? null : $_SESSION['id'];
			// ログインしていなければログインページへ飛ばす
			if($postData['poster'] === null) {
				header('Location: login.php');
			}
			$postData['sentence'] = empty($_POST['sentence']) ? null : htmlspecialchars($_POST['sentence'], ENT_QUOTES, 'UTF-8');
			$postData['pictPath'] = null;
			if(preg_match('/^\([0-9]+\/)', $check)) {
				$postData['comment'] = substr($check, 1, count($check) - 2);
			} else if(preg_match('/[0-9]/', $check)){
				$postData['comment'] = $check;
			} else {
				$postData['comment'] = null;
			}
			$postData['comment'] = $postData['comment'] === null ? null : htmlspecialchars($postData['comment'], ENT_QUOTES, 'UTF-8');

			$board = new BulletinBoard();
			if($board->postThread($postData)) {
				// タイムスタンプを利用しユーザーの最終投稿を取得、投稿した内容が表示されるurlに飛ばす
				header('Location: ./index.php');
				exit();
			} else {
				// 失敗したら閲覧トップページへ
				header('Location: ./index.php');
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
			if(!empty($_POST['mail']) || !empty($_POST['password'])) {
				error_log(print_r('Warning loginCheck.php::POSTの失敗 nmail = '.empty($_POST['mail']).' password ='.empty($_POST['password']).' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
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
				header("Location: index.php");
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
