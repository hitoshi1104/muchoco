<?php
	ini_set('display_errors',1);
	ini_set('def6ault_charset', 'UTF-8');
	session_start();
	require_once('common.php');
	$ProtectInjection = new ProtectInjection();
	$token = $ProtectInjection->getToken();
	$_SESSION['token'] = $token;
	$board = new BulletinBoard();;
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8"/>
		<title>けーじばん</title>
	</head>
	<body>
		<h1>けーじばん</h1>
			<?php
				if(!isset($_SESSION['name'])) {
					echo '<p><b>ログインしていないため閲覧しかできません！</b></p>';
				}
				// url からスレッド全体なのか詳細スレッドなのかを判定する
				$pageNum = empty($_GET['d']) ? 1 : $_GET['d'];
				$check = null;
				if(preg_match("/^\([0-9]+\)/", $pageNum)){
					$pageNum = htmlspecialchars(substr($pageNum, 1, count($pageNum) - 2), ENT_QUOTES, 'UTF-8');
					$check = $pageNum;
					echo createThread($board, $pageNum, $token);
				} else if(preg_match('/[0-9]/', $pageNum)){
					$pageNum = $pageNum;
					echo createBigThread($board, $pageNum);
				} else {
					$pageNum = 1;
					echo createBigThread($board, $pageNum);
				}
			?>
		<br>
		<br>
		<form class="" action="./board.php" method="post">
			<input type="hidden" name="token" value=<?=$token?>>
			<input type="hidden" name="check" value="<?=$check?>">
			<input type="hidden" name="submitSentence" value="submitSentence">
			<textarea name="sentence" value="" rows="4" cols="28" placeholder="144字以内" id="sentence"></textarea> <p id="remaining">残り144</p>
			<button type="submit" name="submitSentence" onclick="return checkSentenceLength()">送信</button>
		</form>
		<br>
		<form class="" action="./board.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="token" value=<?=$token?>>
			<input type="hidden" name="check" value="<?=$check?>">
			<input type="file" name="pictPath" value="" id="pict">
			<br>
			<br>
			<button type="submit" name="submitPict" onclick="return checkFileSize()">画像の送信</button>
		</form>
		<br>
		<a href="./index.php">スレッドに戻る</a>
		<br>
		<?php
			if(!isset($_SESSION['name'])) {
				 $href = '<a href="./login.php">ログイン画面へ</a>';
			 } else {
				$href = '<a href="./logout.php?">ログアウト</a>';
			 }
			 echo $href;
		?>
	</body>
</html>

<script type="text/javascript">
	window.onload = function() {
		document.getElementById('sentence').addEventListener('change', getRemainingLength);
		document.getElementById('sentence').addEventListener('keydown', getRemainingLength);
		document.getElementById('sentence').addEventListener('keyup', getRemainingLength);
		document.getElementById('sentence').addEventListener('keypress', getRemainingLength);
	};

	function getByteLength(str){
	  return encodeURI(str).replace(/%../g, "*").length;
	};

	function checkSentenceLength() {
		let sentence = document.getElementsByName('sentence')[0].value;
		let len = getByteLength(sentence) % 2 === 0 ? getByteLength(sentence) / 2 : Math.floor(getByteLength(sentence) / 2) + 1;
		if(144 < len) {
			alert('投稿は140字以内です！');
			return false;
		}
		return true;
	};

	function getRemainingLength() {
		let sentence = document.getElementsByName('sentence')[0].value;
		let len = getByteLength(sentence) % 2 === 0 ? getByteLength(sentence) / 2 : Math.floor(getByteLength(sentence) / 2) + 1;
		document.getElementById('remaining').textContent = '残り' + (144 - len);
	};

	function checkFileSize() {
		let file = document.getElementById("pict").files;
		if(file.length == 0) {
			alert('画像が選択されていません！');
			return false;
		} else if(1 < file.lenth) {
			alert('送信できる画像は1つまでです！');
			return false;
		}

		if(!file[0].name.toUpperCase().match(/\.(JPG)$/i)
		&& !file[0].name.toUpperCase().match(/\.(JEPG)$/i)
		&& !file[0].name.toUpperCase().match(/\.(PNG)$/i)
		&& !file[0].name.toUpperCase().match(/\.(GIF)$/i)) {
  		alert('送信できるのは画像ファイルのみです！');
			return false;
		}

		if(1000000 < file[0].size) {
			alert('ファイルサイズは1Mまでです！');
			return false;
		}
		return true;
	};
	function deleteCheck(str) {
		if(window.confirm('削除された' + str + 'は復元できません。\n' + str + 'を削除しますか？')){
			return true;
		} else {
			return false;
		}
	};
</script>

<?php
	/**
	* スレッド一覧
	*
	* @param BulletinBoard board
	*	@param int pageNum
	*
	* @return array[][]
	*/
	function createBigThread($boardC, $pageNum) {
		$board = $boardC->getBigThread($pageNum);
		// 描画用配列
		$drawThread = convertThread($board);
		// 描画用に変換する
		$tag = convertBigThreadTag($boardC, $drawThread);
		return $tag;
	}

	/**
	* 詳細スレッドの描画
	*
	* @param BulletinBoard board
	*	@param int threadNo
	*
	* @return String
	*/
	function createThread($boardC, $threadNo, $token) {
		$board = $boardC->getSmallThread($threadNo);
		// 描画用配列
		$drawThread = convertThread($board);
		// 描画用タグ
		$tag = convertSmallThreadTag($drawThread, 'h3', $token);
		return $tag;
	}

	function convertThread($board) {
		$counter = 0;
		$drawThread = [];
		foreach($board as $thread) {
			$drawThread[$counter]['id'] = $thread['no'];
			if($thread['sentence'] !== null) {
				$drawThread[$counter]['sentence'] = $thread['sentence'];
			} else if($thread['pict'] !== null) {
				$drawThread[$counter]['pict'] = $thread['pict'];
			}
			$drawThread[$counter]['timestamp'] = $thread['timestamp'];
			$drawThread[$counter]['commentPoster'] = $thread['poster'];
			$counter ++;
		}
		return $drawThread;
	}

	function convertBigThreadTag($boardC, $drawThread, $t = 'p') {
		$threadSum = count($drawThread);
		$tag = "";
		for($i = 0; $i < $threadSum; $i ++) {
			if(isset($drawThread[$i]['sentence'])) {
				$tag .= '<p class="sentence"><a href="./index.php?d=('.$drawThread[$i]['id'].')">'.$drawThread[$i]['sentence'].'</a><br>投稿日時：'.$drawThread[$i]['timestamp'];
			} else if(isset($drawThread[$i]['pict'])){
				$tag .= '<p class="pict"><a href="./index.php?d=('.$drawThread[$i]['id'].')"><img src="'.$drawThread[$i]['pict'].'" width="128" height="128" border="1"></a><br>投稿日時：'.$drawThread[$i]['timestamp'];
			}
			$tag .= ' コメント('.$boardC->getCommentSum($drawThread[$i]['id']).')</p>'."\n";
		}
		return $tag;
	}

	function convertSmallThreadTag($drawThread, $t = 'p', $token) {
		$tag = '<div style="border:1px solid; margin-bottom:4px">'."\n".'<h2>スレッド</h2>'."\n";
		$threadSum = count($drawThread);
		if(isset($drawThread[0]['sentence'])) {
			$tag .= '<'.$t.' class="sentence">'.$drawThread[0]['sentence'].'<br>投稿日時：'.$drawThread[0]['timestamp'].'</'.$t.'>';
		} else if(isset($drawThread[0]['pict'])){
			$tag .= '<'.$t.' class="pict"><img src="'.$drawThread[0]['pict'].'" width="128" height="128" border="1"><br>投稿日時：'.$drawThread[0]['timestamp'].'</'.$t.'>';
			$tag .= "\n";
		}
		if(isset($_SESSION['id']) && $_SESSION['id'] == $drawThread[0]['commentPoster']) {
			$tag .= '<form style="margin-top:-15px; margin-bottom:30px;" action="./board.php" method="post">'."\n".
							'<input type="hidden" name="token" value="'.$token.'">'."\n".
							'<input type="hidden" name="threadNo" value='.$drawThread[0]['id'].'>'."\n".
							'<button type="submit" name="deleteThread" onclick="return deleteCheck(\'スレッド\')">スレッドの削除</button>'."\n".
							'</form>';
		}
		$tag .="\n".'</div>'."\n";
		$tag .= '<div style="border:1px solid">'."\n".'<h3>寄せられたコメント</h3>'."\n";
		for($i = 1; $i < $threadSum; $i ++) {
			if(isset($drawThread[$i]['sentence'])) {
				$tag .= '<p class="sentence">'.$drawThread[$i]['sentence'].'<br>投稿日時：'.$drawThread[$i]['timestamp'];
			} else if(isset($drawThread[$i]['pict'])){
				$tag .= '<p class="pict"><img src="'.$drawThread[$i]['pict'].'" width="128" height="128" border="1"><br>投稿日時：'.$drawThread[$i]['timestamp'];
			}
			if(isset($_SESSION['id']) && $_SESSION['id'] == $drawThread[$i]['commentPoster']) {
				$tag .= "\n";
				$tag .= '<form style="margin-top:-15px; margin-bottom:30px;" action="./board.php" method="post">'."\n".
								'<input type="hidden" name="token" value="'.$token.'">'."\n".
								'<input type="hidden" name="threadNo" value='.$drawThread[$i]['id'].'>'."\n".
								'<button type="submit" name="deleteComment" onclick="return deleteCheck(\'コメント\')">コメントの削除</button></p>'."\n".
								'</form>';
			} else {
				$tag .= '</p>';
			}
			$tag .= "\n";
		}
		$tag .='</div>';
		return $tag;
	}
?>
