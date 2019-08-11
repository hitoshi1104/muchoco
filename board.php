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

	// ログインチェック
	if(empty($_SESSION['id']) || empty($_SESSION['name'])) {
		$_SESSION = array();
		setcookie(session_name(), '', time() - 1, '/');
		session_destroy();
		header('Location: ./index.php');
		exit();
	}

	if($_POST['token'] !== $_SESSION['token']) {
		$_SESSION = array();
		setcookie(session_name(), '', time() - 1, '/');
		session_destroy();
		header('Location: ./index.php');
		exit();
	}

	// 何をするにも実行者の id を取得
	$postData['poster'] = empty($_SESSION['id']) ? null : htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8');
	// ログインしていなければログインページへ飛ばす
	if($postData['poster'] === null) {
		header('Location: login.php');
	}

	// スレッドの削除
	$threadNo = empty($_POST['threadNo']) ? null : htmlspecialchars($_POST['threadNo'], ENT_QUOTES, 'UTF-8');
	if(isset($_POST['deleteThread']) && $threadNo !== null) {
		$board = new BulletinBoard();
		// 削除する画像のパスを取得する
		$threads = $board->getSmallThread($threadNo);
		$deletePictPath = [];
		$counter = 0;
		foreach($threads as $thread) {
			if($thread['pict'] !== null) {
				$deletePictPath[$counter]['pict'] = $thread['pict'];
			}
			$counter ++;
		}
		if($board->deleteThread($threadNo, $postData['poster'])) {
			foreach($deletePictPath as $path) {
				unlink($path['pict']);
			}
		}
		header('Location: ./index.php');
		exit();
	} else if(isset($_POST['deleteComment']) && $threadNo !== null) {
		$board = new BulletinBoard();
		// 削除する画像のパスを取得する
		$threads = $board->deleteCommentPict($threadNo, $postData['poster']);
		$deletePictPath = [];
		$counter = 0;
		foreach($threads as $thread) {
			if($thread['pict'] !== null) {
				$deletePictPath[$counter]['pict'] = $thread['pict'];
			}
			$counter ++;
		}
		if($board->deleteComment($threadNo, $postData['poster'])) {
			foreach($deletePictPath as $path) {
				unlink($path['pict']);
			}
		}
		header('Location: ./index.php');
		exit();
	} else if($threadNo !== null) {
		header('Location: ./index.php');
		exit();
	}

	// 登録内容を配列にする
	// url からスレッドを立てたのかコメントなのかを調べる
	$check = empty($_POST['check']) ? null : $_POST['check'];
	$check = htmlspecialchars($check, ENT_QUOTES, 'UTF-8');
	$postData['sentence'] = null;
	$postData['pictPath'] = null;
	if(!preg_match('/[0-9]/', $check)){
		$postData['comment'] = null;
	} else {
		$postData['comment'] = $check;
	}

	// テキストの投稿
	if(isset($_POST['submitSentence'])) {
		$postData['sentence'] = empty($_POST['sentence']) ? null : htmlspecialchars($_POST['sentence'], ENT_QUOTES, 'UTF-8');
	} else if(isset($_POST['submitPict'])) {
		if(!isset($_FILES['pictPath']['error']) || !is_int($_FILES['pictPath']['error'])) {
			error_log(print_r('Warning board.php::$_POST[submitPost] 不正なパラメータ ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			header('Location: ./index.php');
			exit();
		}
		// ファイルサイズ制限
		if ($_FILES['pictPath']['size'] > 1000000) {
			// TODO アラートの作成
			header('Location: ./index.php');
			exit();
  	}

		$extension = array_search(mime_content_type($_FILES['pictPath']['tmp_name']),
      array(
					'.gif' => 'image/gif',
          '.jpg' => 'image/jpeg',
          '.png' => 'image/png',
      ),
      true
		);

		if(!$extension) {
			error_log(print_r('Warning board.php::不正な拡張子：'.$extension.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			header('Location: ./index.php');
			exit();
		}

		$fileName = './image/image'.date('YmdHis', time()).'-'.$postData['poster'].$extension;
		$postData['pict'] = $fileName;
		if(is_uploaded_file($_FILES['pictPath']['tmp_name'])) {
	    if(move_uploaded_file($_FILES['pictPath']['tmp_name'], $fileName)) {
				// パーミッションの変更
				chmod($fileName, 0644);
			}
		}
	}
	// DBに登録
	if($postData['sentence'] === null && $postData['pict'] === null) {
		header('Location: ./index.php?d=1');
		exit();
	}
	$board = new BulletinBoard();
	$no = $board->postThread($postData);
	if($check !== null) {
		header('Location: ./index.php?d=('.$check.')');
		exit();
	}
	if(0 < $no) {
		if($postData['comment'] === null) {
			$no = 0 < ($no % 50) ? 1 : $no % 50;
		} else {
			$no = '('.$no.')';
		}
		header('Location: ./index.php?d='.$no);
		exit();
	} else {
		// 失敗したら閲覧トップページへ
		header('Location: ./index.php?d=1');
		exit();
	}
?>
