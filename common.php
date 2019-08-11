<?php
	// csrf 対策
	class ProtectInjection {
		// バイト文字列の長さ
		private $LENGTH = 16;
		// csrf 用トークン
		private $token = null;

		// csrf トークンの生成
		function __construct() {
			$this->token = bin2hex(openssl_random_pseudo_bytes($this->LENGTH));
		}

		public function getToken() {
			return $this->token;
		}
	}

	// DB 周り
	class MyDB {
		private $dbName = 'teacher_muchoco';
		private $host   = 'localhost';
		private $user   = 'daiki';
		private $pass   = 'aykPyRAsUM8phblS';
		protected $dbh    = null;

		// DB へ接続
		protected function connect() {
			$db = 'mysql:dbname='.$this->dbName.';host='.$this->host;
			try {
				$this->dbh = new PDO($db, $this->user, $this->pass);
			} catch(PDOException $e) {
				error_log(print_r('Warning common.php::__construct() DBへの接続に失敗'."\n".$e->getMessage().' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			}
		}
	}

	// ログイン
	class Login extends MyDB {

		function __construct() {
			ini_set("date.timezone", "Asia/Tokyo");
			error_log(print_r('common.php::Login->__construct() DBへの接続 ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$this->connect();
		}
		/**
		* ユーザーの登録　登録に成功したら true を返す
		*
		* @param String mail
		* @param String password
		*
		* @return Boolean
		*/
		public function addUser($mail, $password) {
			if($this->dbh === null) return false;
			$mail = htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
			// すでに登録されているユーザーか調べる
			if($this->existCheckUser($mail)) {
				error_log(print_r('Warning common.php::addUser() user にすでに登録されているメールアドレスのため INSERT 失敗 mail = '.$mail.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return false;
			}
			$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
			$password = password_hash($password, PASSWORD_DEFAULT);

			$sql = 'INSERT INTO user (mail, password) VALUE (:m, :p)';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':m', $mail, PDO::PARAM_STR);
			$prepare->bindValue(':p', $password, PDO::PARAM_STR);
			if($prepare->execute()) {
				error_log(print_r('common.php::addUser() 登録アドレス：'.$mail.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return true;
			} else {
				error_log(print_r('Warning common.php::addUser() user に INSERT 失敗 '.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return false;
			}
		}

		/**
		* ユーザーが登録されているか調べる 登録されていたら true を返す
		*
		* @param String mail
		*
		* @return Boolean
		*/
		private function existCheckUser($mail) {
			if($this->dbh === null) return false;
			$sql = 'SELECT count(id) FROM user WHERE mail=:m;';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':m', $mail, PDO::PARAM_STR);
			$prepare->execute();
			$column = $prepare->fetchColumn();
			if($column === '0') return false;
			return true;
		}

		/**
		* メールアドレスとパスワードが一致するかチェックし、一致したら [[id][name]] array を返す
		*
		* @param String mail
		* @param String password
		*
		* @return array
		*/
		public function loginCheck($mail, $password) {
			if($this->dbh === null) return [];
			$mail = htmlspecialchars($mail, ENT_QUOTES, 'UTF-8');
			$sql = 'SELECT * FROM user WHERE mail=:m';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':m', $mail, PDO::PARAM_STR);
			$prepare->execute();
			$result = $prepare->fetchAll(PDO::FETCH_ASSOC);
			// 見つかったレコードが1件以外は認めない
			if(count($result) !== 1) {
				error_log(print_r('Warning common.php::loginCheck() レコード発見数：'.count($result).' 登録アドレス：'.$mail.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return [];
			}
			// パスワードが一致しなければからの配列を返す
			$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
			if(!password_verify($password, $result[0]['password'])) {
				error_log(print_r('Warning common.php::loginCheck() パスワード入力ミス 登録アドレス：'.$mail.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return [];
			}
			// パスワードが一致したらパスワードを抜いた id と name の配列を返す
			$arr['id'] = $result[0]['id'];
			$arr['name'] = $result[0]['name'];
			error_log(print_r('common.php::loginCheck() ログイン完了 userId：'.$arr['id'].' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			return $arr;
		}
	}

	// 掲示板
	class BulletinBoard extends MyDB {

		function __construct() {
			error_log(print_r('common.php::BulletinBoard->__construct() DBへの接続 ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$this->connect();
		}

		/**
		* 指定されたページ番号からスレッドを50個返す
		*
		*	@param int pageNum = 1
		*
		* @return array
		*/
		public function getBigThread($pageNum = 1) {
			if($this->dbh === null) return [];
			error_log(print_r('common.php::BulletinBoard->getBigThread() pageNum：'.$pageNum.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'SELECT * FROM board WHERE comment IS NULL ORDER BY no LIMIT :p1, :p2';
			$prepare = $this->dbh->prepare($sql);
			// $pageNum = $pageNum == 1 ? 1 : ($pageNum - 1) * 50;
			$pageNum = ($pageNum - 1) * 50;
			$prepare->bindValue(':p1', $pageNum, PDO::PARAM_INT);
			$pageNum += 50;
			$prepare->bindValue(':p2', $pageNum, PDO::PARAM_INT);
			$prepare->execute();
			$boardThread = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $boardThread;
		}

		/**
		* 投稿する 成功したら登録した id を返す
		* 				失敗したら 0 を返す
		*
		* @param int boardId
		* @param array[userId][sentence][pictPath][commentId] postData
		*
		* @return int
		*/
		public function postThread($postData) {
			error_log(print_r('common.php::BulletinBoard->postThread() userId：'.$postData['poster'].' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			error_log(print_r($postData, true), '3', 'log.txt');
			if($this->dbh === null) return false;
			$sql = 'INSERT INTO board (poster, sentence, pict, comment) VALUES (:pos, :sen, :pic, :com)';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':pos', $postData['poster'], PDO::PARAM_INT);
			$prepare->bindValue(':sen', $postData['sentence'], PDO::PARAM_STR);
			$prepare->bindValue(':pic', $postData['pict'], PDO::PARAM_STR);
			$prepare->bindValue(':com', $postData['comment'], PDO::PARAM_STR);
			if($prepare->execute()) {
				error_log(print_r('common.php::BulletinBoard->postThread() ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return $this->getLastRecordId();
			} else {
				error_log(print_r('Warning common.php::BulletinBoard->postThread() ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
				return 0;
			}
		}

		/**
		* コメントの数を取得する
		*
		*	@param int comment
		*
		* @return int
		*/
		public function getCommentSum($comment) {
			if(!isset($comment)) return 0;
			if(!is_numeric($comment)) return 0;
			if($this->dbh === null) return 0;
			// error_log(print_r('common.php::BulletinBoard->getCommentSum() commentSum：'.$comment.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'SELECT count(no) FROM board WHERE comment = :c';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':c', $comment, PDO::PARAM_INT);
			$prepare->execute();
			$column = $prepare->fetchColumn();
			return $column;
		}

		/**
		* 追加された最後のレコードの id を返す
		*/
		private function getLastRecordId() {
			// error_log(print_r('common.php::BulletinBoard->postThread() no：'.'['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			if($this->dbh === null) return false;
			$sql = 'SELECT no FROM board ORDER BY no DESC LIMIT 1;';
			$prepare = $this->dbh->prepare($sql);
			$prepare->execute();
			$no = $prepare->fetch(PDO::FETCH_ASSOC);
			error_log(print_r($no, true), '3', 'log.txt');
			$no = $no['no'];
			if(0 < $no) {
				return $no;
			} else {
				return 0;
			}
		}

		/**
		* 個々のスレッドを取得する
		*
		* @param int threadNo
		*
		* @return array
		*/
		public function getSmallThread($threadNo) {
			if($this->dbh === null) return [];
			error_log(print_r('common.php::BulletinBoard->getBigThread() threadNo：'.$threadNo.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'SELECT * FROM board WHERE no=:n OR comment=:n ORDER BY no ASC';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':n', $threadNo, PDO::PARAM_INT);
			$prepare->execute();
			$boardThread = $prepare->fetchAll(PDO::FETCH_ASSOC);
			return $boardThread;
		}

		/**
		* コメントの削除 成功したら true を返す
		*
		* @param int threadId
		* @param int poster
		*
		* @return boolean
		*/
		public function deleteComment($threadNo, $poster) {
			if($this->dbh === null) return false;
			error_log(print_r('common.php::BulletinBoard->deleteComment() threadNo：'.$threadNo.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'DELETE FROM board WHERE no=:n AND poster=:p';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':n', $threadNo, PDO::PARAM_INT);
			$prepare->bindValue(':p', $poster, PDO::PARAM_INT);
			if($prepare->execute()) return ture;
			return false;
		}

		/**
		* 削除対象のコメントのパスを返す
		*
		* @param int threadId
		* @param int poster
		*
		* @return String
		*/
		public function deleteCommentPict($threadNo, $poster) {
			if($this->dbh === null) return false;
			error_log(print_r('common.php::BulletinBoard->deleteCommentPict() threadNo：'.$threadNo.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'SELECT pict FROM board WHERE no=:n AND poster=:p';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':n', $threadNo, PDO::PARAM_INT);
			$prepare->bindValue(':p', $poster, PDO::PARAM_INT);
			$prepare->execute();
			return $prepare->fetchAll(PDO::FETCH_ASSOC);
		}

		/**
		* スレッドの削除 成功したら true を返す
		*
		* @param int threadId
		* @param int poster
		*
		* @return boolean
		*/
		public function deleteThread($threadNo, $poster) {
			if($this->dbh === null) return false;
			error_log(print_r('common.php::BulletinBoard->deleteThread() threadNo：'.$threadNo.' poster：'.$poster.' ['.date('Y-m-d H:i:s', time()).']'."\n", true), '3', 'log.txt');
			$sql = 'DELETE FROM board WHERE (no=:n AND poster=:p) OR comment=:n';
			$prepare = $this->dbh->prepare($sql);
			$prepare->bindValue(':n', $threadNo, PDO::PARAM_INT);
			$prepare->bindValue(':p', $poster, PDO::PARAM_INT);
			if($prepare->execute()) return ture;
			return false;
		}
	}
?>
