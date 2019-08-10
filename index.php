<?php
	ini_set('display_errors',1);
	session_start();
	require_once('common.php');
	$ProtectInjection = new ProtectInjection();
	$token = $ProtectInjection->getToken();
	$_SESSION['token'] = $token;
	echo '<pre>';
	var_dump($_SESSION);
	echo '</pre>';
	$board = new BulletinBoard();
	// echo '<pre>';
	// var_dump($board->getBigThread());
	// echo '</pre>';
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>けーじばん</title>
	</head>
	<body>
		<h1>けーじばん</h1>
		<!-- <pre> -->
			<?php
				echo convertDrawThread($board, createBigThread($board));
		 	?>
		<!-- </pre> -->
		<br>
		<br>
		<form class="" action="./board.php" method="post">
			<input type="hidden" name="token" value=<?=$token?>>
			<textarea name="sentence" value="" rows="4" cols="28"></textarea>
			<br>
			<input type="submit" name="submitSentence" value="送信">
		</form>
		<br>
		<form class="" action="./board.php" method="post">
			<input type="hidden" name="token" value=<?=$token?>>
			<input type="file" name="pictPath" value="">
			<br>
			<input type="submit" name="submitPict" value="画像の送信">
		</form>
		<br>
		<a href="./login.php">ログイン画面へ</a>
	</body>
</html>

<?php
	/**
	* スレッド一覧
	*
	* @param BulletinBoard board
	*
	* @return array[][]
	*/
	function createBigThread($board) {
		$pageNum = explode('/', $_SERVER['HTTP_REFERER']);
		$pageNum = $pageNum[count($pageNum) - 1];
		if(preg_match('/[0-9]/', $pageNum)){
			$pageNum = $pageNum;
		} else {
			$pageNum = 1;
		}
		$board = $board->getBigThread($pageNum);
		// 描画用配列
		$drawThread = [];
		$counter = 0;
		foreach($board as $thread) {
			$drawThread[$counter]['id'] = $thread['no'];
			if($thread['sentence'] !== null) {
				$drawThread[$counter]['sentence'] = $thread['sentence'];
			} else if($thread['pict'] !== null) {
				$drawThread[$counter]['pict'] = $thread['pict'];
			}
			$drawThread[$counter]['timestamp'] = $thread['timestamp'];
			$counter ++;
		}
		// var_dump($drawThread);
		return($drawThread);
	}

	// html と php の分離
	/** スレッド内容を描画の形に成形する
	*
	* @param BulletinBoard board
	* @param array[][] thread
	*
	*	@return String
	*/
	function convertDrawThread($board, $thread) {
		$threadSum = count($thread);
		$tag = "";
		for($i = 0; $i < $threadSum; $i ++) {
			if(isset($thread[$i]['sentence'])) {
				$tag .= '<p class="sentence"><a href="./index.php/'.$thread[$i]['id'].'">'.$thread[$i]['sentence'].'</a><br>投稿日時：'.$thread[$i]['timestamp'];
			} else if(!isset($thread[$i]['pict'])){
				$tag .= '<p class="pict"><a href="./index.php/'.$thread[$i]['id'].'">'.$thread[$i]['pict'].'</a><br>投稿日時：'.$thread[$i]['timestamp'];
			}
			$tag .= ' コメント('.$board->getCommentSum($thread[$i]['id']).')</p>';
		}
		return $tag;
	}
?>
